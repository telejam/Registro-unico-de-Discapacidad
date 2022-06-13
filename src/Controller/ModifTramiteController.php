<?php

namespace App\Controller;

use App\Entity\EstadoTramite;
use App\Entity\ModificacionTramite;
use App\Entity\Tramite;
use App\Form\ModifTramiteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifTramiteController extends AbstractController
{
    /**
     * @Route("/seguimientos", name="seguimientos")
     */
    public function getAll(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $seguimientos = $em->getRepository(ModificacionTramite::class)->findAll();

        return $this->render('modif_tramite/index.html.twig', [
            'controller_name' => 'ModifTramiteController',
            'seguimientos' => $seguimientos
        ]);
    }
    
    /**
     * @Route("/createseguimiento", name="createseguimiento")
     */
    public function create(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $seguimiento = new ModificacionTramite;
        $id = $request->query->get('id');
        $tramite = $em->getRepository(Tramite::class)->find($id);
        $numeroexpediente = $tramite->getNumeroexpediente();
        
        //uno sirve, el otro no, a descubrirlo
        $historial = $em->getRepository(ModificacionTramite::class)->findAll($numeroexpediente);
        $historial = $em->getRepository(ModificacionTramite::class)->findBy(['tramite'=>$numeroexpediente]);


        dump($historial);

        $seguimiento->setTramite($tramite);
        $form = $this->createForm(ModifTramiteType::class, $seguimiento);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form['Finalizar']->getData()) {
                $estadotramite = $em->getRepository(EstadoTramite::class)->find(2);
                $tramite->setFecharesolucion($seguimiento->getFechaseguimiento()); 
                $tramite->setEstadotramite($estadotramite);
            }

            $em->persist($seguimiento);
            $em->flush();
            return $this->redirectToRoute('seguimientos');
        }
        return $this->render('modif_tramite/create.html.twig', [
            'seguimientos'=>$historial,
            'formulario'=>$form->createView()
        ]);     
    }
}
