<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Turno;
use BcTic\CamSotBundle\Form\TurnoType;

/**
 * Turno controller.
 *
 * @Route("/turnos")
 */
class TurnoController extends Controller
{

    /**
     * Show Turno Calendar
     *
     * @Route("/calendar", name="turnos_calendar", defaults={ })
     * @Method("GET")
     * @Template()
     */
    public function calendarAction()
    {
        return array();
    }


    /**
     * Lists all Turno entities.
     *
     * @Route("/index/{page}", name="turnos_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:Turno')->findBy(
              array(),
              array('dia' => 'ASC'),
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
     * Creates a new Turno entity.
     *
     * @Route("/add", name="turnos_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:Turno:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Turno();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('turnos_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Turno entity.
    *
    * @param Turno $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Turno $entity)
    {
        $form = $this->createForm(new TurnoType(), $entity, array(
            'action' => $this->generateUrl('turnos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Turno entity.
     *
     * @Route("/new", name="turnos_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Turno();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Turno entity.
     *
     * @Route("/edit/{id}", name="turnos_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Turno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Turno entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a Turno entity.
    *
    * @param Turno $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Turno $entity)
    {
        $form = $this->createForm(new TurnoType(), $entity, array(
            'action' => $this->generateUrl('turnos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing Turno entity.
     *
     * @Route("/update/{id}", name="turnos_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:Turno:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Turno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Turno entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('turnos_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a Turno entity.
     *
     * @Route("/show/{id}", name="turnos_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Turno')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Turno entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a Turno entity.
     *
     * @Route("/delete/{id}/{token}", name="turnos_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:Turno')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find Turno entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('turnos_index'));
    }

}
