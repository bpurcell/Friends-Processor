<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jpgraph {
    function barchart($ydata, $title='Bar Chart')
    {
        require_once("jpgraph/jpgraph.php");
        require_once("jpgraph/jpgraph_bar.php");    
        
        // Create the graph. These two calls are always required
        $graph = new Graph(500,300);
        $graph->SetScale("textint");
        $graph->xaxis->title->Set('Month');
        $graph->yaxis->title->Set('Visitors count');
        $graph->yaxis->SetTitleMargin(40);
        
        $graph->ygrid->Show(true,true);
        $graph->xgrid->Show(true,false);
        
        $graph->img->SetAntiAliasing(false);
        
        $a = $gDateLocale->GetShortMonth();
        $graph->xaxis->SetTickLabels($a);
              
        // Setup title
        $graph->title->Set($title);
        
        $graph->SetMargin(53,30,45,60);
                
        $timer = new JpgTimer();
        $timer->Push();
        $graph->footer->right->Set('Generated (ms): ');
        $graph->footer->right->SetFont(FF_COURIER,FS_ITALIC);
        $graph->footer->SetTimer($timer);
        
        // Create the linear plot
        $lineplot=new BarPlot($ydata);
        $lineplot->SetColor("blue");
        $lineplot->value->SetFormat("%d");
        $lineplot->value->Show();
        
        // Add the plot to the graph
        $graph->Add($lineplot);
        
        return $graph; // does PHP5 return a reference automatically?
    }
}