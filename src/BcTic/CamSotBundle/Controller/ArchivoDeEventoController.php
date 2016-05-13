<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\ArchivoDeEvento;
use BcTic\CamSotBundle\Form\ArchivoDeEventoType;

/**
 * ArchivoDeEvento controller.
 *
 * @Route("/archivos_de_eventos")
 */
class ArchivoDeEventoController extends Controller
{

    /**
     * Lists all ArchivoDeEvento entities.
     *
     * @Route("/{id}/download.php", name="archivos_de_evento_download", defaults={ "id" = -1 })
     * @Method("GET")
     * @Template()
     */
    public function downloadAction($id)
    {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->find($id);

      if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeEvento entity.');
      }

      $filename = $entity->getUploadRootDir().$entity->getCreatedAt().'-'.$entity->getPath();

      // Generate response
      $response = new Response();
      // Set headers
      $response->headers->set('Cache-Control', 'private');
      $response->headers->set('Content-type', mime_content_type($filename));
      $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity->getPath()) . '";');
      $response->headers->set('Content-length', filesize($filename));
      // Send headers before outputting anything
      $response->sendHeaders();
      $response->setContent(readfile($filename));

      return $response;

    }        

    /**
     * Lists all ArchivoDeEvento entities.
     *
     * @Route("/index/{page}", name="archivos_de_eventos_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->findBy(
              array(),
              array('createdAt' => 'DESC'),
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
     * Creates a new ArchivoDeEvento entity.
     *
     * @Route("/add", name="archivos_de_eventos_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:ArchivoDeEvento:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ArchivoDeEvento();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $entity->upload();

            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('archivos_de_eventos_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a ArchivoDeEvento entity.
    *
    * @param ArchivoDeEvento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ArchivoDeEvento $entity)
    {
        $form = $this->createForm(new ArchivoDeEventoType(), $entity, array(
            'action' => $this->generateUrl('archivos_de_eventos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new ArchivoDeEvento entity.
     *
     * @Route("/new", name="archivos_de_eventos_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {

        $entity = new ArchivoDeEvento();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ArchivoDeEvento entity.
     *
     * @Route("/edit/{id}", name="archivos_de_eventos_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeEvento entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a ArchivoDeEvento entity.
    *
    * @param ArchivoDeEvento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ArchivoDeEvento $entity)
    {
        $form = $this->createForm(new ArchivoDeEventoType(), $entity, array(
            'action' => $this->generateUrl('archivos_de_eventos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing ArchivoDeEvento entity.
     *
     * @Route("/update/{id}", name="archivos_de_eventos_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:ArchivoDeEvento:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeEvento entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('archivos_de_eventos_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a ArchivoDeEvento entity.
     *
     * @Route("/show/{id}", name="archivos_de_eventos_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeEvento entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
 
    /**
     * Mark as re-process  a ArchivoDeEvento entity.
     *
     * @Route("/reprocess/{id}", name="archivos_de_eventos_reprocess")
     * @Method("GET")
     * @Template()
     */
    public function reprocessAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeEvento entity.');
        }

        $entity->setStatus('PENDING');
        $em->persist($entity);
        $em->flush();

         $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

        return $this->redirect($this->generateUrl('archivos_de_eventos_index', array('id' => $id)));

    }

}
