<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use BcTic\CamSotBundle\Entity\CuadrillaDeEvento;
use BcTic\CamSotBundle\Form\CuadrillaDeEventoFilterType;

/**
 * CuadrillaDeEvento controller.
 *
 * @Route("/movil")
 */
class CuadrillaDeEventoController extends Controller
{

    /**
     *
     * @Route("/new/index", name="movil_new_index")
     * @Method("GET")
     * @Template()
     */
    public function indexNewAction()
    {

      $entity = new CuadrillaDeEvento();  
      $form = $this->createFilterForm($entity, $this->generateUrl('moviles_search'));

      return array(
        'filter' => $form->createView(),
        );
    }

    private function createFilterForm(CuadrillaDeEvento $entity, $route)
    {
        $form = $this->createForm(new CuadrillaDeEventoFilterType(), $entity, array(
            'action' => $route,
            'method' => 'POST',
        ));

        $form->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class'=>'search')));

        return $form;
    }


    /**
     * Lists all Search entities.
     *
     * @Route("/search.json", name="moviles_search")
     * @Method("POST")
     * @Template()
     */
    public function searchAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        //Nemo 3 es el codigo del movil
        $movil = $request->get('valor');

        $sql = "SELECT DISTINCT(valor) FROM CuadrillaDeEvento WHERE valor LIKE '%".$movil."%' ORDER BY valor ASC LIMIT 30 OFFSET ".(30 * ($request->get('page',1) - 1)).";";                     
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = array();
        foreach ($stmt->fetchAll() as $entity) {
          $data[] = array(
            'valor' => $entity['valor'],
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

    /**
     * @Route("/search_events.json", name="moviles_eventos_search")
     * @Method("POST")
     * @Template()
     */
    public function showEventsAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        //Nemo 3 es el codigo del movil
        $movil = $request->get('valor');

        $sql = "SELECT Evento.nombre FROM Evento INNER JOIN CuadrillaDeEvento ON CuadrillaDeEvento.evento_id = Evento.id AND CuadrillaDeEvento.valor = '".$movil."' ORDER BY Evento.nombre ASC LIMIT 30 OFFSET ".(30 * ($request->get('page',1) - 1)).";";                     
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = array();
        foreach ($stmt->fetchAll() as $entity) {
          $data[] = array(
            'valor' => $entity['nombre'],
            );
        }

      return new JsonResponse(array('results' => $data, 'length' => count($data),'page' => (int) $request->get('page',1)));
    }
}
