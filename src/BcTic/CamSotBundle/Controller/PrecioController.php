<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Precio;
use BcTic\CamSotBundle\Form\PrecioType;

/**
 * Precio controller.
 *
 * @Route("/precios")
 */
class PrecioController extends Controller
{

    /**
     * Lists all Precio entities.
     *
     * @Route("/index/{page}", name="precios_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        //10 is the page size
        $entities = $em->getRepository('BcTicCamSotBundle:Precio')->findBy(
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
     * Creates a new Precio entity.
     *
     * @Route("/add", name="precios_create")
     * @Method("POST")
     * @Template("BcTicCamSotBundle:Precio:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Precio();
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

            return $this->redirect($this->generateUrl('precios_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Precio entity.
    *
    * @param Precio $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Precio $entity)
    {
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('precios_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Precio entity.
     *
     * @Route("/new", name="precios_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Precio();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Precio entity.
     *
     * @Route("/edit/{id}", name="precios_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Precio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
        }

        $editForm = $this->createEditForm($entity);


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
    * Creates a form to edit a Precio entity.
    *
    * @param Precio $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Precio $entity)
    {
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('precios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Edits an existing Precio entity.
     *
     * @Route("/update/{id}", name="precios_update")
     * @Method("PUT")
     * @Template("BcTicCamSotBundle:Precio:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Precio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se grabaron correctamente.'
            );

            return $this->redirect($this->generateUrl('precios_index', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Finds and displays a Precio entity.
     *
     * @Route("/show/{id}", name="precios_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Precio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }
    /**
     * Deletes a Precio entity.
     *
     * @Route("/delete/{id}/{token}", name="precios_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id, $token)
    {

        $csrf = $this->get('form.csrf_provider');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BcTicCamSotBundle:Precio')->find($id);

        if (!$entity) {
              throw $this->createNotFoundException('Unable to find Precio entity.');
        }

        if ($csrf->isCsrfTokenValid('entity'.$entity->getId(), $token)) {
            $em->remove($entity);
            $em->flush();

            //BORRO TODOS LOS PRECIOS CREADOS:
            $sql = "DELETE FROM precio_item WHERE precio_id = ".$id.";";
            $em->getConnection()->exec($sql);
            $em->flush();    

             $this->get('session')->getFlashBag()->add(
              'notice',
              'Los datos se borraron correctamente.'
            );
        }


        return $this->redirect($this->generateUrl('precios_index'));
    }

    /**
     *
     * @Route("/{fecha_desde}/{fecha_hasta}/download-precios.html", name="precio_download", defaults={})
     * @Method("GET")
     * @Template()
     */
    public function downloadAction($fecha_desde,$fecha_hasta)
    {

      //renderizo una vista:
      $em = $this->getDoctrine()->getManager();

      $sql = "SELECT UCASE(MARCA_RECURSO_INSUFICIENTE) as MARCA_RECURSO_INSUFICIENTE, UCASE(CUADRILLA_DESASIGNADA) as CUADRILLA_DESASIGNADA , COD_MOVIL, TIPO_MOVIL, UCASE(ESTADO) as ESTADO, UCASE(ESTADO_DE_FINALIZACION) as ESTADO_DE_FINALIZACION, CODIGO_AMBITO, CODIGO_ELEMENTO_RESPONSABLE, CODIGO_CONDICION  FROM cierre WHERE ESTADO NOT IN ('ANULADO','CANCELADO') AND STR_TO_DATE(INICIO,'%d-%m-%Y') BETWEEN STR_TO_DATE('".$fecha_desde."','%Y-%m-%d') AND STR_TO_DATE('".$fecha_hasta."','%Y-%m-%d') GROUP BY MARCA_RECURSO_INSUFICIENTE, CUADRILLA_DESASIGNADA, COD_MOVIL, TIPO_MOVIL, ESTADO,ESTADO_DE_FINALIZACION, CODIGO_AMBITO, CODIGO_ELEMENTO_RESPONSABLE, CODIGO_CONDICION ORDER BY TIPO_MOVIL, ESTADO, ESTADO_DE_FINALIZACION, CODIGO_AMBITO, CODIGO_ELEMENTO_RESPONSABLE,CODIGO_CONDICION;";                     
      $stmt = $em->getConnection()->prepare($sql);
      $stmt->execute();

      $data = $stmt->fetchAll();

      $filename = $fecha_desde.'_'.$fecha_hasta.'_precios.csv';

      // Generate response
      $response = $this->render('BcTicCamSotBundle:Precio:download.csv.twig',array('data' => $data));
      // Set headers
      $response->headers->set('Cache-Control', 'private');
      $response->headers->set('Content-type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) .'";');
      // Send headers before outputting anything
      $response->sendHeaders();
      

      return $response;

    }  

    /**
     * 
     *
     * @Route("/{id}/download-archivo.php", name="precio_archivo_download", defaults={ "id" = -1 })
     * @Method("GET")
     * @Template()
     */
    public function downloadArchivoAction($id)
    {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('BcTicCamSotBundle:Precio')->find($id);

      if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
      }

      $filename = $entity->getUploadRootDir().$entity->getPath();

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


}
