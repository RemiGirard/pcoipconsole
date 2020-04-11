<?php

namespace App\Controller;

use App\Entity\Terminal;
use App\Service\operateTerminal;
use App\Form\AddTerminalType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TerminalController extends AbstractController
{
    /**
     * @IsGranted("ROLE_OPERATOR")
     * @Route("/terminal/list", name="terminal_list")
     */
    public function terminallist(OperateTerminal $operateTerminal)
    {
        //find all terminals by role in the database
        $repository = $this->getDoctrine()->getRepository(Terminal::class);
        $clients= $repository->findByRole('client');
        $hosts = $repository->findByRole('host');

	// render the table with the list of clients and hosts
        return $this->render('pcoipconsole/list.html.twig', [
        	'clients' => $clients ,
        	'hosts' => $hosts ,
    	]);
    }

    /**
     * @IsGranted("ROLE_OPERATOR")
     * @Route("/terminal/configuration", name="terminal_configuration")
     **/
    public function terminalconfiguration()
    {
        //configuration page to update terminals info and create new terminal
        return $this->render('pcoipconsole/configuration.html.twig');
    }

    /**
     *  * @IsGranted("ROLE_OPERATOR")
     *   * @Route("/terminal/infoupdate/{all}", name="terminal_allinfoupdate")
     *    **/
    public function terminalinfoupdate($all, OperateTerminal $operateTerminal, ValidatorInterface $validator)
    {
        // used only in configuration page to update all information of terminals or just the connections information then redirect to the configuration page
        $log = '';
        //get Terminal class db methods and then find all terminals
        $repository = $this->getDoctrine()->getRepository(Terminal::class);
        $terminals = $repository->findAll();

        $entityManager = $this->getDoctrine()->getManager();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        foreach ($terminals as $terminal) {
            $ping = $operateTerminal->ping($terminal);
            $terminal->setPing($ping);
            $connectedToTerminal = '';

	    // if the terminal is pinged then it will try to connect to it
	    // if it's possible to log to the web interface it will update identification information
	    // if it can't ping or log to the interface, it will set to empty all information related to terminal connection but will keep the identification information like the hostname, the label and the role                                                                         
            if ($ping == 'true') {
                $logged = $operateTerminal->initConnection($terminal);
                $terminal->setLogged($logged);
                if ($logged == 'logged') {
			// depending on the user request it will try to update identification information
			if ($all == "true") {
                        $name = $operateTerminal->getName($terminal);
                        $terminal->setName($name);
    
                        $label = $operateTerminal->getLabel($terminal);
                        $terminal->setLabel($label);
    
                        $role = $operateTerminal->getRole($terminal);
                        $terminal->setRole($role);
                        $log .= ' all ';
                    }
		    //  always update connection information
                    $terminal->setConnectionState($operateTerminal->getConnectionState($terminal));
                    $connectedTo = $operateTerminal->getConnectedTo($terminal);
                    $repository = $this->getDoctrine()->getRepository(Terminal::class);
                    $hostTerminal= $repository->findOneByIp($connectedTo);
		    
		    //if the connected terminal is on the database it will set the connectedTo value to the id of the connected terminal, and the connectedToLabel to the label of the connected terminal
                    if ($hostTerminal) {
                        $terminal->setConnectedTo($hostTerminal->getId());
                        $terminal->setConnectedToLabel($hostTerminal->getLabel());
			$connectedToTerminal = $hostTerminal;
                    } else {
		    // if the connected terminal is not on the database it will set connectedTo and connectedToLabel to the ip address of the connected terminal
                        $terminal->setConnectedTo($connectedTo);
                        $terminal->setConnectedToLabel($connectedTo);
			$connectedToTerminal = '';
                    }
                } else {
		    // if can't log to the terminal > reset connection information
                    $terminal->setConnectedTo('');
                    $terminal->setConnectedToLabel('');
                    $terminal->setConnectionState('disconnected');
                }
	    } else {
		// if can't ping > reset connection information
                $terminal->setConnectedTo('');
                $terminal->setConnectedToLabel('');
                $terminal->setConnectionState('disconnected');
            }
        }
                                                                                       
  	// verify terminal information validity before send to the database 
        $errors = $validator->validate($terminal);
        if (count($errors) > 0) {
            return new Response();
        }
        $entityManager->persist($terminal);
        $entityManager->flush();
                                                                                       
        $data = $serializer->serialize($terminal, 'json');
        $dataConnectedHost = $serializer->serialize($connectedToTerminal, 'json');
                                                                                           
        $log .= 'terminal '.$terminal->getId().'all info updated';

        return $this->redirectToRoute('terminal_configuration');
    }


    /**
    * @IsGranted("ROLE_OPERATOR")
    * @Route("/terminal/create", name="terminal_create")
    */
    public function createTerminal(Request $request, ValidatorInterface $validator)
    {
	// create a form from AddTerminalType
        $terminal = new Terminal();
        $form = $this->createForm(AddTerminalType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // add a terminal in the DB from ATerminalType form
            $terminal = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($terminal);
            $entityManager->flush();

            return $this->redirectToRoute('terminal_operate', [ 'id' => $terminal->getId() ]);
        }

        return $this->render('pcoipconsole/addTerminal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_OPERATOR")
     * @Route("/terminal/{id}/edit", name="terminal_edit")
     */
    public function editTerminal(Request $request, Terminal $terminal)
    {
        // fill the form with information from DB, on application: update all the fields
        // terminal is selected with ParamConverter: it uses {id} to look for a Terminal in the DB
        $form = $this->createForm(AddTerminalType::class, $terminal);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $terminal = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($terminal);
            $entityManager->flush();
    
            return $this->redirectToRoute('terminal_operate', [ 'id' => $terminal->getId() ]);
        }

        return $this->render('pcoipconsole/addTerminal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_OPERATOR")
     * @Route("/terminal/{id}/delete", name="terminal_delete")
     */
    public function deleteTerminal(Terminal $terminal)
    {
        if (!$terminal) {
            throw $this->createNotFoundException('Terminal not found');
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($terminal);
        $em->flush();

        return $this->redirectToRoute('terminal_list');
    }

    /**
     * @IsGranted("ROLE_OPERATOR")
     * @Route("/terminal/{id}", name="terminal_operate", methods={"GET"})
     **/
    public function operate(Terminal $terminal, OperateTerminal $operateTerminal)
    {
        // display information of a Terminal
        return $this->render('pcoipconsole/operate.html.twig', [
            'terminal' => $terminal
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_OPERATOR")
     *
     * @Route("/terminal/{id}", name="terminal_operation", methods={"POST"})
     **/
    public function operateTerminalController(Terminal $terminal, Request $request, OperateTerminal $operateTerminal, ValidatorInterface $validator)
    {
        // Called by ajax request it use Service/operateTerminal actions and return specific $data (json) with $log (string)
        $log = '';
        $data = "";
        $entityManager = $this->getDoctrine()->getManager();

        // convert the post request in an array $params
        $params = array();
        $request= $request->getContent();
        if (!empty($request)) {
            $params = json_decode($request, true);
        }
        $log .= 'json request: ';

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        // verify if all the parameters of the Terminal object, most importantly it verify the ipaddress and hostname which will be used in a shell command
        $terminalInfoErrors= $validator->validate($terminal);

        $action = $params['action'] ?: "";

        if (count($terminalInfoErrors) == 0) {
            $log .= 'terminal:';
            $log .= $terminal->getId();
                                                        
            // call ../Service/operateTerminal action
            switch ($action) {
            case 'ping':
                $ping = $operateTerminal->ping($terminal);
                $terminal->setPing($ping);
                $log .= $ping;
                $data = $ping;
                break;
            case 'init':
                $logged = $operateTerminal->initConnection($terminal);
                $terminal->setLogged($logged);
                $data = $logged;
                $log .= $logged;
                break;
            case 'connect':
                $log .= $operateTerminal->connect($terminal);
                break;
            case 'disconnect':
                $log .= $operateTerminal->disconnectTerminal($terminal);
                break;
            case 'reboot':
                    $log .= $operateTerminal->reboot($terminal);
                    break;
            case 'changeHost':
                // same as previous client verification, verify the parameters of the Terminal object, most importantly it verify the ipddress and hostname which will be used in a shell command
                $repository = $this->getDoctrine()->getRepository(Terminal::class);
                $hostTerminal= $repository->findOneById($params['hostId']);
                $hostTerminalInfoErrors= $validator->validate($hostTerminal);
                if (count($hostTerminalInfoErrors) == 0) {
                    $log .= $operateTerminal->changeHost($terminal, $hostTerminal);
                } else {
                    $log .= 'Host terminal is not valid';
                }
                break;
            case 'getName':
                $name = $operateTerminal->getName($terminal);
                $terminal->setName($name);
                $data = $name;
                $log .= 'getName:'.$name;
                break;
            case 'getLabel':
                $label = $operateTerminal->getLabel($terminal);
                $terminal->setLabel($label);
                $data = $label;
                $log .= 'getLabel:'.$label;
                break;
            case 'getRole':
                $role = $operateTerminal->getRole($terminal);
                $terminal->setRole($role);
                $data = $role;
                $log .= 'getRole:'.$role;
                break;
            case 'getIp':
                $ip = $operateTerminal->getIp($terminal);
                $terminal->setIp($ip);
                $data = $ip;
                $log.= 'getIp'.$ip;
                break;
            case 'getConnectedTo':
                $terminal->setConnectionState($operateTerminal->getConnectionState($terminal));
                $connectedTo = $operateTerminal->getConnectedTo($terminal);
                $data = $connectedTo;
                $log .= 'getConnectedTo:'.$connectedTo;

                $repository = $this->getDoctrine()->getRepository(Terminal::class);
                $hostTerminal= $repository->findOneByIp($connectedTo);

        // try to identify the connected terminal if it is in the DB it use its id and label if not, it will just use the IP adress
                if ($hostTerminal) {
                    $terminal->setConnectedTo($hostTerminal->getId());
                    $terminal->setConnectedToLabel($hostTerminal->getLabel());
                } else {
                    $terminal->setConnectedTo($connectedTo);
                    $terminal->setConnectedToLabel($connectedTo);
                }
                break;
            case 'updateInfoFromIp':
                $ping = $operateTerminal->ping($terminal);
                    $terminal->setPing($ping);
                $connectedToTerminal = null;

                if ($ping == 'true') {
                    $logged = $operateTerminal->initConnection($terminal);
                    $terminal->setLogged($logged);

                    if ($logged == 'logged') {
                        if ($params['allInfo'] == "true") {
                            $name = $operateTerminal->getName($terminal);
                            $terminal->setName($name);
                            
                            $label = $operateTerminal->getLabel($terminal);
                            $terminal->setLabel($label);
            
                            $role = $operateTerminal->getRole($terminal);
                            $terminal->setRole($role);
                            $log .= ' all ';
                        }

                        $terminal->setConnectionState($operateTerminal->getConnectionState($terminal));
                        $connectedTo = $operateTerminal->getConnectedTo($terminal);

                        $repository = $this->getDoctrine()->getRepository(Terminal::class);
                        $hostTerminal= $repository->findOneByIp($connectedTo);
                        if ($hostTerminal) {
                            $terminal->setConnectedTo($hostTerminal->getId());
                            $terminal->setConnectedToLabel($hostTerminal->getLabel());
                        } else {
                            $terminal->setConnectedTo($connectedTo);
                            $terminal->setConnectedToLabel($connectedTo);
                        }
                    }
                } else {
                    $terminal->setLogged('Can\'t login in terminal');
                    $terminal->setConnectedTo("");
                    $terminal->setConnectedToLabel("");
                    $terminal->setConnectionState('disconnected');
                    $terminal->setPing("false");
                }
                $data = $serializer->serialize($terminal, 'json');
                
                $log .= 'terminal '.$terminal->getId().'info updated';
                break;
            case 'updateIpFromHostname':
                $ip = $operateTerminal->getIp($terminal->getName());
                $data .= $ip;
                $log .= $ip;
                $terminal->setIp($ip);
                break;
            case 'getConnectionState':
                $connectionState = $operateTerminal->getConnectionState($terminal);
                $data = $connectionState;
                $log .= 'getConnectionState:'.$connectionState;
                $terminal->setConnectionState($connectionState);
                break;
            case 'getBitRate':
                $bitRate = $operateTerminal->getBitRate($terminal);
                $data = (object) ['tx' => $bitRate["tx"], 'rx' => $bitRate["rx"]];
                $log .= ' getBitRate: tx: '.$bitRate["tx"]." kbps - rx: ".$bitRate["rx"]." kbps";
                break;
            default:
                $log .= 'No corresponding action';
    
        }
            // verify the validity of information $terminal properties and add the $terminal to the DB
            $errors = $validator->validate($terminal);
            if (count($errors) == 0) {
                $entityManager->persist($terminal);
                $entityManager->flush();
            } else {
                $log .= 'New values not valid';
            }
        } else {
            $log .= 'This terminal is not valid';
            $data = " ";
        }
        $response = new JsonResponse([
            'log' => $log,
            'data'=> $data
        ]);
        return $response;
    }
}
