<?php

namespace BcTic\CamSotBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use BcTic\CamSotBundle\Entity\Evento;
use BcTic\CamSotBundle\Entity\PropiedadDeEvento;
use BcTic\CamSotBundle\Form\EventoType;
use BcTic\CamSotBundle\Form\EventoFilterType;

/**
 * Evento controller.
 *
 * @Route("/eventos")
 */
class EventoController extends Controller
{

    /**
     * Lists all Evento entities.
     *
     * @Route("/new/index", name="eventos_new_index")
     * @Method("GET")
     * @Template()
     */
    public function indexNewAction()
    {

      $evento = new Evento();  
      $evento->setStatus("NEW");
      $form = $this->createFilterForm($evento, $this->generateUrl('eventos_search'));
      //Evento STATUS = NEW

      return array(
        'filter' => $form->createView(),
        );
    }

    private function createFilterForm(Evento $entity, $route)
    {
        $form = $this->createForm(new EventoFilterType(), $entity, array(
            'action' => $route,
            'method' => 'POST',
        ));

        $form->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class'=>'search')));

        return $form;
    }

    /**
     * Lists all Search entities.
     *
     * @Route("/search.json", name="eventos_search")
     * @Method("POST")
     * @Template()
     */
    public function searchAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        //Nemo 8 es fecha de inicio
        $fecha = date_create_from_format('d-m-Y',$request->get('fechaDeInicio'));

        $entities = $em->getRepository('BcTicCamSotBundle:Evento')
                      ->createQueryBuilder('e')
                      ->leftJoin('e.propiedadesDeEvento','p')
                      ->where('e.nombre LIKE :nombre AND e.status = :status AND p.nemo = 8 AND p.valor LIKE :fecha')
                      ->setParameters(
                        array(
                          'nombre' => '%'.$request->get('nombre').'%',
                          'status' => $request->get('status'),
                          'fecha' => $fecha->format('d-m-Y').'%',
                          )
                        )
                      ->setMaxResults(30)
                      ->setFirstResult( 30 * ($request->get('page',1) - 1))
                      ->orderBy('e.nombre', 'ASC')
                      ->getQuery()
                      ->getResult();


        $data = array();
        foreach ($entities as $entity) {
          $createdAt = \DateTime::createFromFormat('U', $entity->getCreatedAt());
          $data[] = array(
            'id' => $entity->getId(),
            'nombre' => $entity->getNombre(),
            'createdAt' => $createdAt->format('d-m-Y h:j'),
            'fechaDeInicio' => ($entity->getPropiedadDeEvento(8) instanceof PropiedadDeEvento) ? $entity->getPropiedadDeEvento(8)->getValor() : null,
            );
        }

      return new JsonResponse(array('results' => $data, 'page' => (int) $request->get('page',1)));
    }

    /**
     * Lists all Evento entities.
     *
     * @Route("/closed/index/{page}", name="eventos_closed_index", defaults={ "page" = 1 })
     * @Method("GET")
     * @Template()
     */
    public function indexClosedAction($page)
    {

      $evento = new Evento();  
      $evento->setStatus("CLOSED");
      $form = $this->createFilterForm($evento, $this->generateUrl('eventos_search'));
      //Evento STATUS = CLOSED

      return array(
        'filter' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Evento entity.
     *
     * @Route("/show/{nombre}", name="eventos_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($nombre)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BcTicCamSotBundle:Evento')->findBy(
            array('nombre' => $nombre),
            array(),
            1,
            0
            );

        if (count($entity) != 1) {
            throw $this->createNotFoundException('Unable to find Evento entity.');
        }

        $entity = $entity[0];

        $csrf = $this->get('form.csrf_provider');


        return array(
            'entity'      => $entity,
            'csrf' => $csrf,
        );
    }

}
