<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\TipoDeEvento;
use BcTic\CamSotBundle\Form\TipoDeEventoType;

/**
 * TipoDeEvento controller.
 *
 * @Route("/tipos_de_evento")
 */
class TipoDeEventoController extends Controller
{

    /**
     * Lists all TipoDeEvento entities.
     *
     * @Route("/index/{page}", name="tipos_de_evento_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:TipoDeEvento')->findBy(
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
     * Creates a new TipoDeEvento entity.
     *
     * @Route("/add", name="tipos_de_evento_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:TipoDeEvento:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new TipoDeEvento();
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

            return $this->redirect($this->generateUrl('tipos_de_evento_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a TipoDeEvento entity.
    *
    * @param TipoDeEvento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(TipoDeEvento $entity)
    {
        $form = $this->createForm(new TipoDeEventoType(), $entity, array(
            'action' => $this->generateUrl('tipos_de_evento_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new TipoDeEvento entity.
     *
     * @Route("/new", name="tipos_de_evento_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new TipoDeEvento();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing TipoDeEvento entity.
     *
     * @Route("/edit/{id}", name="tipos_de_evento_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:TipoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TipoDeEvento entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a TipoDeEvento entity.
    *
    * @param TipoDeEvento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(TipoDeEvento $entity)
    {
        $form = $this->createForm(new TipoDeEventoType(), $entity, array(
            'action' => $this->generateUrl('tipos_de_evento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing TipoDeEvento entity.
     *
     * @Route("/update/{id}", name="tipos_de_evento_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:TipoDeEvento:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:TipoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TipoDeEvento entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('tipos_de_evento_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a TipoDeEvento entity.
     *
     * @Route("/show/{id}", name="tipos_de_evento_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:TipoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TipoDeEvento entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a TipoDeEvento entity.
     *
     * @Route("/delete/{id}/{token}", name="tipos_de_evento_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:TipoDeEvento')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find TipoDeEvento entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('tipos_de_evento_index'));
    }

}
