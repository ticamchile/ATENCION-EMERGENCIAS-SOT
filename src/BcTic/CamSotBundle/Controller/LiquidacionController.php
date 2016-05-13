<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Liquidacion;
use BcTic\CamSotBundle\Form\LiquidacionType;

/**
 * Liquidacion controller.
 *
 * @Route("/liquidacion")
 */
class LiquidacionController extends Controller
{

    /**
     * Lists all Liquidacion entities.
     *
     * @Route("/index/{page}", name="liquidacion_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:Liquidacion')->findBy(
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
     * Creates a new Liquidacion entity.
     *
     * @Route("/add", name="liquidacion_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:Liquidacion:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Liquidacion();
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

            return $this->redirect($this->generateUrl('liquidacion_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Liquidacion entity.
    *
    * @param Liquidacion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Liquidacion $entity)
    {
        $form = $this->createForm(new LiquidacionType(), $entity, array(
            'action' => $this->generateUrl('liquidacion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Liquidacion entity.
     *
     * @Route("/new", name="liquidacion_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Liquidacion();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Liquidacion entity.
     *
     * @Route("/show/{id}", name="liquidacion_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Liquidacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidacion entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a Liquidacion entity.
     *
     * @Route("/delete/{id}/{token}", name="liquidacion_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:Liquidacion')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find Liquidacion entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('liquidacion_index'));
    }

    /**
     *
     * @Route("/{id}/download-{tipo}.html", name="liquidacion_download", defaults={ "id" = -1, "tipo" = "red" })
     * @Method("GET")
     * @Template()
     */
    public function downloadAction($id,$tipo)
    {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('BcTicCamSotBundle:Liquidacion')->find($id);

      if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidacion entity.');
      }

      $filename = $this->container->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$entity->getId().'-'.$tipo.'-data.csv';

      if (file_exists($filename)) {
        // Generate response
        $response = new Response();
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename('liquidacion-'.$entity->getId().'-'.$tipo.'-data.csv') . '";');
        $response->headers->set('Content-length', filesize($filename));
        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

      } else {

        // Generate response
        $response = new Response();
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename('liquidacion-'.$entity->getId().'-'.$tipo.'-data.csv') . '";');
        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent("ARCHIVO DE LIQUIDACION ".strtoupper($tipo)." NO SE HA GENERADO AUN.");        

      }

      return $response;

    }    


    /**
     * Lists all Liquidacion entities.
     *
     * @Route("/{id}/download-precios-faltantes.html", name="liquidacion_faltantes_download", defaults={ "id" = -1 })
     * @Method("GET")
     * @Template()
     */
    public function downloadPreciosAction($id)
    {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('BcTicCamSotBundle:Liquidacion')->find($id);

      if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidacion entity.');
      }

      $filename = $this->container->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$entity->getId().'-precios-faltantes-data.csv';

        // Generate response
        $response = new Response();
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename('liquidacion-'.$entity->getId().'-precios-faltantes-data.csv') . '";');
        $response->headers->set('Content-length', filesize($filename));
        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

      return $response;

    }    


    /**
     * Mark as re-process  a Liquidacion entity.
     *
     * @Route("/reprocess/{id}", name="liquidacion_reprocess")
     * @Method("GET")
     * @Template()
     */
    public function reprocessAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Liquidacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidacion entity.');
        }

        $entity->setStatus('PENDING');
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('liquidacion_index', array('id' => $id)));

    }   


}
