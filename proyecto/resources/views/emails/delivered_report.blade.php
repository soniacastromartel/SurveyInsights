<div>
{{ $emailData['message'] }} <br>
   <p>
   Compruebe el adjunto en este correo
   </p>

   <div>
      <!-- cambiar public_path por base_path al ponerlo en producción -->
   <img src="{{ $message->embed(public_path() . '/assets/img/LogoICOT.png') }}" width="100px" id="logoIcot" alt="ICOT Icon" /> 
   </div>
</div>