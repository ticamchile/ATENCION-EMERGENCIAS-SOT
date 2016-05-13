<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Periodo;
use BcTic\CamSotBundle\Form\PeriodoType;

/**
 * Periodo controller.
 *
 * @Route("/periodos")
 */
class PeriodoController extends Controller
{

    /**
     * Lists all Periodo entities.
     *
     * @Route("/index/{page}", name="periodos_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:Periodo')->findBy(
              array(),
              array('orden' => 'ASC','inicio' => 'ASC'),
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
     * Creates a new Periodo entity.
     *
     * @Route("/add", name="periodos_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:Periodo:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Periodo();
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

            return $this->redirect($this->generateUrl('periodos_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Periodo entity.
    *
    * @param Periodo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Periodo $entity)
    {
        $form = $this->createForm(new PeriodoType(), $entity, array(
            'action' => $this->generateUrl('periodos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Periodo entity.
     *
     * @Route("/new", name="periodos_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Periodo();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Periodo entity.
     *
     * @Route("/edit/{id}", name="periodos_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Periodo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periodo entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a Periodo entity.
    *
    * @param Periodo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Periodo $entity)
    {
        $form = $this->createForm(new PeriodoType(), $entity, array(
            'action' => $this->generateUrl('periodos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing Periodo entity.
     *
     * @Route("/update/{id}", name="periodos_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:Periodo:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Periodo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periodo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('periodos_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a Periodo entity.
     *
     * @Route("/show/{id}", name="periodos_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Periodo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periodo entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a Periodo entity.
     *
     * @Route("/delete/{id}/{token}", name="periodos_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:Periodo')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find Periodo entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('periodos_index'));
    }

}
