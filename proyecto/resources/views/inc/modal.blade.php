<div class="modal fade" id="modal-template" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: white;background-position: center top;background-repeat: repeat;">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="exampleModalLongTitle">FORMULARIO DE ENVÍO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="contact" class="modal-body">
                <div style="margin-bottom:20px;">
                    Complete los siguientes datos para el envío del informe
                </div>

                <fieldset>
                    <input placeholder="Remitente" type="email" tabindex="1" name="name" required autofocus>
                </fieldset>
                <fieldset>
                    <input placeholder="Destinatario" type="email" tabindex="2" name="name" required autofocus>
                </fieldset>
                <fieldset>
                    <input placeholder="Asunto" type="text" tabindex="3" name="name" required autofocus>
                </fieldset>
                <fieldset>
                    <textarea placeholder="Escriba su mensaje aquí...." tabindex="5" name="message" required></textarea>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button id="btnConfirmRequest" type="button" class="btn btn-success">ENVIAR</button>
                <button type="button" class="btn btn-red-icot" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    fieldset {
        border: medium none !important;
        margin: 0 0 10px;
        min-width: 100%;
        padding: 0;
        width: 100%;
    }

    #contact input[type="text"],
    #contact input[type="email"],
    #contact textarea {
        width: 100%;
        border: 1px solid #ccc;
        background: #FFF;
        margin: 0 0 5px;
        padding: 10px;
    }

    #contact input[type="text"]:hover,
    #contact input[type="email"]:hover,
    #contact textarea:hover {
        -webkit-transition: border-color 0.3s ease-in-out;
        -moz-transition: border-color 0.3s ease-in-out;
        transition: border-color 0.3s ease-in-out;
        border: 1px solid #aaa;
    }

    #contact textarea {
        height: 100px;
        max-width: 100%;
        resize: none;
    }

    #contact input:focus,
    #contact textarea:focus {
        outline: 0;
        border: 1px solid #aaa;
    }

    ::-webkit-input-placeholder {
        color: #888;
    }

    :-moz-placeholder {
        color: #888;
    }

    ::-moz-placeholder {
        color: #888;
    }

    :-ms-input-placeholder {
        color: #888;
    }
</style>