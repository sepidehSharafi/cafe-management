<?php

namespace App\Controller;

use App\Form\AnalyticType;
use App\Service\AnalyticService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticController extends AbstractController
{
    #[Route('/admin/analytics', name: 'admin_analytics')]
    public function showAnalytics(Request $request, AnalyticService $analyticService): Response
    {
        $form = $this->createForm(AnalyticType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $startDate = $data['startDate']; 
            $endDate = $data['endDate']; 
    
            return $this->redirectToRoute('admin_analytics_report', [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]);
        }
    
        return $this->render('analytic/analytics.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/analytics/report', name: 'admin_analytics_report')]
    public function showAnalyticsReport(Request $request, AnalyticService $analyticService): Response
    {
        $data = $request->query->all();
        $salesOverTime = $analyticService->getSalesOverTime($data['startDate'], $data['endDate']);

        return $this->render('analytic/analytics_report.html.twig', [
            'salesOverTime' => $salesOverTime,
        ]);
    }
}
