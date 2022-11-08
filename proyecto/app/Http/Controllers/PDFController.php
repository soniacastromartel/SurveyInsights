<?php
  
namespace App\Http\Controllers;
  
use PDF;
use Mail;
  
class PDFController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        $data["email"] = "soniacastromartel@gmail.com";
        $data["title"] = "Correo Prueba";
        $data["body"] = "This is Demo";

        $pdf = PDF::loadView('preview_data', $data);

 
        // $files = [
        //     public_path('report.pdf'),
        //     public_path('portada.pdf'),
        // ];
  
        // Mail::send('myTestMail', $data, function($message)use($data, $files) {
        //     $message->to($data["email"], $data["email"])
        //             ->subject($data["title"]);
 
        //     foreach ($files as $file){
        //         $message->attach($file);
        //     }
            
        // });
 
        Mail::send('preview_data', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attachData($pdf->output(), "text.pdf");
        });
        dd('Correo enviado con éxito');
    }
}