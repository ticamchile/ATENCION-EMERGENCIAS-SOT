<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\ArchivoDeCierre;
use BcTic\CamSotBundle\Form\ArchivoDeCierreType;

/**
 * ArchivoDeCierre controller.
 *
 * @Route("/archivodecierre")
 */
class ArchivoDeCierreController extends Controller
{

    /**
     * Lists all ArchivoDeCierre entities.
     *
     * @Route("/index/{page}", name="archivodecierre_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:ArchivoDeCierre')->findBy(
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
     * Creates a new ArchivoDeCierre entity.
     *
     * @Route("/add", name="archivodecierre_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:ArchivoDeCierre:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ArchivoDeCierre();
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

            return $this->redirect($this->generateUrl('archivodecierre_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a ArchivoDeCierre entity.
    *
    * @param ArchivoDeCierre $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ArchivoDeCierre $entity)
    {
        $form = $this->createForm(new ArchivoDeCierreType(), $entity, array(
            'action' => $this->generateUrl('archivodecierre_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new ArchivoDeCierre entity.
     *
     * @Route("/new", name="archivodecierre_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ArchivoDeCierre();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Deletes a ArchivoDeCierre entity.
     *
     * @Route("/delete/{id}/{token}", name="archivodecierre_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeCierre')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find ArchivoDeCierre entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('archivodecierre_index'));
    }

    /**
     * Mark as re-process  a ArchivoDeCierre entity.
     *
     * @Route("/reprocess/{id}", name="archivodecierre_reprocess")
     * @Method("GET")
     * @Template()
     */
    public function reprocessAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeCierre')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeCierre entity.');
        }

        $entity->setStatus('NEW');
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('archivodecierre_index', array('id' => $id)));

    }   

    /**
     * Lists all ArchivoDeEvento entities.
     *
     * @Route("/{id}/download.html", name="archivodecierre_download", defaults={ "id" = -1 })
     * @Method("GET")
     * @Template()
     */
    public function downloadAction($id)
    {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('BcTicCamSotBundle:ArchivoDeCierre')->find($id);

      if (!$entity) {
            throw $this->createNotFoundException('Unable to find ArchivoDeCierre entity.');
      }

      $filename = $this->container->get('kernel')->getRootDir().'/Resources/data/'.$entity->getPath();

      // Generate response
      $response = new Response();
      // Set headers
      $response->headers->set('Cache-Control', 'private');
      $response->headers->set('Content-type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity->getPath()) . '";');
      $response->headers->set('Content-length', filesize($filename));
      // Send headers before outputting anything
      $response->sendHeaders();
      $response->setContent(readfile($filename));

      return $response;

    }  

}
