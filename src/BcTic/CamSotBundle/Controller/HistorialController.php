<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Historial;
use BcTic\CamSotBundle\Form\HistorialType;

/**
 * Historial controller.
 *
 * @Route("/historiales")
 */
class HistorialController extends Controller
{

    /**
     * Lists all Historial entities.
     *
     * @Route("/index/{page}", name="historiales_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:Historial')->findBy(
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
     * Creates a new Historial entity.
     *
     * @Route("/add", name="historiales_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:Historial:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Historial();
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

            return $this->redirect($this->generateUrl('historiales_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Historial entity.
    *
    * @param Historial $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Historial $entity)
    {
        $form = $this->createForm(new HistorialType(), $entity, array(
            'action' => $this->generateUrl('historiales_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Historial entity.
     *
     * @Route("/new", name="historiales_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Historial();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Historial entity.
     *
     * @Route("/edit/{id}", name="historiales_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Historial')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historial entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a Historial entity.
    *
    * @param Historial $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Historial $entity)
    {
        $form = $this->createForm(new HistorialType(), $entity, array(
            'action' => $this->generateUrl('historiales_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing Historial entity.
     *
     * @Route("/update/{id}", name="historiales_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:Historial:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Historial')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historial entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('historiales_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a Historial entity.
     *
     * @Route("/show/{id}", name="historiales_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Historial')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historial entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a Historial entity.
     *
     * @Route("/delete/{id}/{token}", name="historiales_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:Historial')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find Historial entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('historiales_index'));
    }

}
