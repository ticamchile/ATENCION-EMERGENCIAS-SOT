<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\RolDeTurno;
use BcTic\CamSotBundle\Entity\RolEnTurno;
use BcTic\CamSotBundle\Form\RolDeTurnoType;

/**
 * RolDeTurno controller.
 *
 * @Route("/roles_de_turno")
 */
class RolDeTurnoController extends Controller
{

    /**
     * Lists all RolDeTurno entities.
     *
     * @Route("/index/{page}", name="roles_de_turno_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->findBy(
              array(),
              array('id' => 'DESC'),
              10,
              10 * ($page - 1)

        );

        $csrf = $this->get('form.csrf_provider');

        return array(
            'page' => $page,
            'entities' => $entities,
            'csrf' => $csrf,
        );
    }

     /**
     * Lists all RolDeTurno entities of the month.
     *
     * @Route("/{turno_tipo}/index.json", name="roles_de_turno_json", defaults={ "turno_tipo" = "DOMICILIO" })
     * @Method("GET")
     * @Template()
     */
    public function jsonAction($turno_tipo)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BcTicCamSotBundle:RolDeTurno')
                      ->createQueryBuilder('r')
                      ->leftJoin('r.turno','t')
                      ->where('t.dia = :dia')
                      ->setParameters(
                        array(
                          'dia' => $turno_tipo,
                          )
                        )
                      ->orderBy('r.id', 'DESC')
                      ->getQuery()
                      ->getResult();

        //Convert to EventObject:
        $obj = array();

        
        foreach ($entities as $entity) {

          $start = \DateTime::createFromFormat('Y-m-d H:i',$entity->getFecha()->format('Y-m-d').' '.$entity->getTurno()->getPeriodo()->getInicio()->format('H:i'));
          $end = \DateTime::createFromFormat('Y-m-d H:i',$entity->getFecha()->format('Y-m-d').' '.$entity->getTurno()->getPeriodo()->getFin()->format('H:i'));

          if ($end < $start) $end->add(new \DateInterval('P1D'));

          $obj[] = array(
            'id' => $entity->getId(),
            'title' => $entity->__toString(),
            'allDay' => false,
            'start' => $start->format('c'),
            'end' => $end->format('c'),
            'editable' => true,
            'startEditable' => true,
            'url' => $this->generateUrl('roles_de_turno_show', array('id' => $entity->getId())),
            'color' => $this->get('translator')->trans('COLOR_'.$entity->getTurno()->getDia()),   // a non-ajax option
            'textColor' =>'#FFF', // a non-ajax option
            );
        }

        return new JsonResponse($obj);

    }

    /**
     * Update the date of a Turno entity.
     *
     * @Route("/update_date", name="roles_de_turno_date_update")
     * @Method("POST")
     * @Template("")
     */
    public function updateDateAction(Request $request)
    {

      $id = $request->request->get('id'); 
      //Busco el objecto:
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
      }

      $incomingDate = $request->request->get('date'); 

      //$incomingDate 2014-09-03T07:00:00
      $date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $incomingDate);

      $entity->setFecha($date);
      $em->persist($entity);
      $em->flush();
       
      $obj = array('status' => 'OK', 'id' => $id, 'date' => $date->format('Y-m-d'));

      return new JsonResponse($obj);
    }

    /**
     * Clone the date of a Turno entity.
     *
     * @Route("/clone_date", name="roles_de_turno_date_clone")
     * @Method("POST")
     * @Template("")
     */
    public function cloneDateAction(Request $request)
    {

      $id = $request->request->get('id'); 
      //Busco el objecto:
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
      }

      $incomingDate = $request->request->get('date'); 

      //$incomingDate 2014-09-03T07:00:00
      $date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $incomingDate);

      $newEntity = new RolDeTurno();
      $newEntity->setTurno($entity->getTurno());
      $newEntity->setFecha($date);
      $em->persist($newEntity);
      $em->flush();

       foreach ($entity->getRolesEnTurno() as $rolEnTurno) {
        $nuevoRol = new RolEnTurno();
        $nuevoRol->setRol($rolEnTurno->getRol());
        $nuevoRol->setRegistro($rolEnTurno->getRegistro());
        $nuevoRol->setRolDeTurno($newEntity);
        $em->persist($nuevoRol);
      }  

      $em->flush();
       
      $obj = array('status' => 'OK', 'id' => $id, 'date' => $date->format('Y-m-d'));

      return new JsonResponse($obj);
    }    

    /**
     * Creates a new RolDeTurno entity.
     *
     * @Route("/add", name="roles_de_turno_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:RolDeTurno:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new RolDeTurno();
        //Lo creo con 3 entidades obligatoriamente:
        $entity->addRolesEnTurno(new RolEnTurno());
        $entity->addRolesEnTurno(new RolEnTurno());
        $entity->addRolesEnTurno(new RolEnTurno());

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //Recorro los objectos de la collección:
            foreach ($entity->getRolesEnTurno() as $rol) {
              $rol->setRolDeTurno($entity);
              $em->persist($rol);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('turnos_calendar', array()));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a RolDeTurno entity.
    *
    * @param RolDeTurno $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(RolDeTurno $entity)
    {
        $form = $this->createForm(new RolDeTurnoType(), $entity, array(
            'action' => $this->generateUrl('roles_de_turno_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new RolDeTurno entity.
     *
     * @Route("/new/{date}.html", name="roles_de_turno_new", requirements={"date" = "^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$"}, defaults= { "date": "2014-09-23" })
     * @Method("GET")
     * @Template()
     */
    public function newAction($date)
    {
        $entity = new RolDeTurno();

        $entity->setFecha(\DateTime::createFromFormat('Y-m-d',$date));

        $em = $this->getDoctrine()->getManager();

        //Tres: RolEnTurno:
        $supervisores = $em->getRepository('BcTicCamSotBundle:Rol')->findBy(
             array('nemo' => 'SUP'),
             array(),
             1
            );

        if (!$supervisores[0]) {
          throw $this->createNotFoundException('Unable to find Rol entity.');
        }

        $supervisorEnTurno = new RolEnTurno();
        $supervisorEnTurno->setRol($supervisores[0]);

        $entity->addRolesEnTurno($supervisorEnTurno); 

        $maestros = $em->getRepository('BcTicCamSotBundle:Rol')->findBy(
             array('nemo' => 'MTR'),
             array(),
             1
            );

        if (!$maestros[0]) {
          throw $this->createNotFoundException('Unable to find Rol entity.');
        }

        $maestroEnTurno = new RolEnTurno();
        $maestroEnTurno->setRol($maestros[0]);

        $entity->addRolesEnTurno($maestroEnTurno);

        $choferes = $em->getRepository('BcTicCamSotBundle:Rol')->findBy(
             array('nemo' => 'CH'),
             array(),
             1
            );

        if (!$choferes[0]) {
          throw $this->createNotFoundException('Unable to find Rol entity.');
        }

        $choferEnTurno = new RolEnTurno();
        $choferEnTurno->setRol($choferes[0]);

        $entity->addRolesEnTurno($choferEnTurno);        

        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing RolDeTurno entity.
     *
     * @Route("/edit/{id}", name="roles_de_turno_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
        }

        $editForm = $this->createEditForm($entity);

        $csrf = $this->get('form.csrf_provider');

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'csrf' => $csrf,
        );
    }

    /**
    * Creates a form to edit a RolDeTurno entity.
    *
    * @param RolDeTurno $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(RolDeTurno $entity)
    {
        $form = $this->createForm(new RolDeTurnoType(), $entity, array(
            'action' => $this->generateUrl('roles_de_turno_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing RolDeTurno entity.
     *
     * @Route("/update/{id}", name="roles_de_turno_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:RolDeTurno:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $csrf = $this->get('form.csrf_provider');

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('turnos_calendar', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'csrf' => $csrf,
        );
    }

    /**
     * Finds and displays a RolDeTurno entity.
     *
     * @Route("/show/{id}.html", name="roles_de_turno_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
        }

        $csrf = $this->get('form.csrf_provider');

        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }

    /**
     * Finds and displays a RolDeTurno entity.
     *
     * @Route("/print/{fecha_desde}/{fecha_hasta}/{domicilio}-{libreDomicilio}-{licenciaMedicaDomicilio}-{red}-{libreRed}-{licenciaMedicaRed}/print.html", name="roles_de_turno_print")
     * @Method("GET")
     * @Template()
     */
    public function printAction($fecha_desde, $fecha_hasta, $domicilio, $libreDomicilio,$licenciaMedicaDomicilio,$red,$libreRed,$licenciaMedicaRed)
    {
        $em = $this->getDoctrine()->getManager();

        $data = array();

        $turnos = $em->getRepository('BcTicCamSotBundle:Turno')
                      ->createQueryBuilder('t')
                      ->orderBy('t.dia', 'DESC')
                      ->groupBy('t.dia')
                      ->getQuery()
                      ->getResult();

        $periodos = array();
        $i = 0;
        foreach ($turnos as $turno) {

            if ($domicilio == 0) if ($turno->getDia() == 'DOMICILIO') continue;
            if ($libreDomicilio == 0) if ($turno->getDia() == 'LIBRE_DOMICILIO') continue;
            if ($licenciaMedicaDomicilio == 0) if ($turno->getDia() == 'LICENCIA_MEDICA_DOMICILIO') continue;

            if ($red == 0) if ($turno->getDia() == 'RED') continue;
            if ($libreRed == 0) if ($turno->getDia() == 'LIBRE_RED') continue;
            if ($licenciaMedicaRed == 0) if ($turno->getDia() == 'LICENCIA_MEDICA_RED') continue;

            $data[$i] = array('id' => $turno->getId(), 'dia' => $turno->getDia(), 'periodos' => array());

            //Busco los periodos de este dia:
            $entities = $em->getRepository('BcTicCamSotBundle:Turno')
                      ->createQueryBuilder('t')
                      ->innerJoin('t.periodo','p')
                      ->where('t.dia = :turno_dia')
                      ->setParameters(array('turno_dia' => $turno->getDia()))
                      ->orderBy('p.orden', 'ASC')
                      ->getQuery();         

            foreach($entities->getResult() as $periodoTurno) {
                $data[$i]['periodos'][] = array('id' => $periodoTurno->getId(), 'nombre' => $periodoTurno->getPeriodo()->getNombre(), 'inicio' => $periodoTurno->getPeriodo()->getInicio(), 'fin' => $periodoTurno->getPeriodo()->getFin()); 
            }

          $i++;      
        }     


        return array('data' => $data, 'fecha_desde' => date_create($fecha_desde), 'fecha_hasta' => date_create($fecha_hasta));
    }

     /**
     * 
     *
     * @Route("/{turno_id}/{fecha}/print.json", name="roles_de_turno_print_json")
     * @Method("GET")
     * @Template()
     */
    public function printJsonAction($turno_id, $fecha)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BcTicCamSotBundle:RolDeTurno')
                      ->createQueryBuilder('r')
                      ->leftJoin('r.turno','t')
                      ->where('t.id = :turno_id AND r.fecha = :turno_fecha')
                      ->setParameters(
                        array(
                          'turno_id' => $turno_id,
                          'turno_fecha' => $fecha,
                          )
                        )
                      ->orderBy('r.id', 'DESC')
                      ->getQuery()
                      ->getResult();

        //Convert to EventObject:
        $obj = array();

        
        foreach ($entities as $entity) {

          $start = \DateTime::createFromFormat('Y-m-d H:i',$entity->getFecha()->format('Y-m-d').' '.$entity->getTurno()->getPeriodo()->getInicio()->format('H:i'));
          $end = \DateTime::createFromFormat('Y-m-d H:i',$entity->getFecha()->format('Y-m-d').' '.$entity->getTurno()->getPeriodo()->getFin()->format('H:i'));

          if ($end < $start) $end->add(new \DateInterval('P1D'));

          $obj[] = array(
            'id' => $entity->getId(),
            'title' => $entity->__toString(),
            'start' => $start->format('c'),
            'end' => $end->format('c'),
            
            );
        }

        return new JsonResponse($obj);

    }


    /**
     * Deletes a RolDeTurno entity.
     *
     * @Route("/delete/{id}/{token}", name="roles_de_turno_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:RolDeTurno')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find RolDeTurno entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('turnos_calendar'));
    }

}
