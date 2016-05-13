<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\Cierre;
use BcTic\CamSotBundle\Form\CierreFilterType;

/**
 * CuadrillaDeEvento controller.
 *
 * @Route("/cierre")
 */
class CierreController extends Controller
{

    /**
     *
     * @Route("/new/index", name="cierre_new_index")
     * @Method("GET")
     * @Template()
     */
    public function indexNewAction()
    {

      $entity = new Cierre();  
      $form = $this->createFilterForm($entity, $this->generateUrl('cierres_search'));

      return array(
        'filter' => $form->createView(),
        );
    }

    private function createFilterForm(Cierre $entity, $route)
    {
        $form = $this->createForm(new CierreFilterType(), $entity, array(
            'action' => $route,
            'method' => 'POST',
        ));

        $form->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class'=>'search')));

        return $form;
    }


    /**
     * Lists all Search entities.
     *
     * @Route("/search.json", name="cierres_search")
     * @Method("POST")
     * @Template()
     */
    public function searchAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        //Nemo 3 es el codigo del movil
        $archivoDeCierre = $request->get('archivodecierre');
        
        $sql = " SELECT COD_MOVIL, EMPRESA, CUADRILLA_DESASIGNADA, TIPO_EVENTO ,count(*) as HITS FROM cierre WHERE archivo_de_cierre_id = '".$archivoDeCierre."' GROUP BY COD_MOVIL, TIPO_EVENTO, CUADRILLA_DESASIGNADA ORDER BY EMPRESA ASC,COD_MOVIL ASC, HITS DESC LIMIT 30 OFFSET ".(30 * ($request->get('page',1) - 1)).";";                     
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = array();
        foreach ($stmt->fetchAll() as $entity) {
          $data[] = array(
            'movil' => $entity['COD_MOVIL'],
            'empresa' => $entity['EMPRESA'],
            'hits' => $entity['HITS'],
            'tipo_evento' => $entity['TIPO_EVENTO'],
            'cuadrilla_desasignada' => $entity['CUADRILLA_DESASIGNADA'],
            );
        }

      return new JsonResponse(array('results' => $data, 'length' => count($data),'page' => (int) $request->get('page',1)));
    }

    /**
     * Finds and displays a CuadrillaDeEvento entity.
     *
     * @Route("/show/{valor}", name="movil_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($valor)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BcTicCamSotBundle:CuadrillaDeEvento')->findBy(array('valor' => $valor));

        if (count($entities) == 0) {
            throw $this->createNotFoundException('Unable to find CuadrillaDeEvento entity.');
        }

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entities'  => $entities,
            'valor' => $valor,
            'csrf' => $csrf,
        );
    }

}
