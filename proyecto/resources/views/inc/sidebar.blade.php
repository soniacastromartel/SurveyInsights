<div class="sidebar" data-color="danger" data-background-color="grey" {{-- data-image="../assets/img/sidebar-1.jpg"--}}>

    <!--
      Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

      Tip 2: you can also add an image using data-image tag
  -->
    <div class="sidebar-wrapper d-flex flex-column">
        <img src="{{ asset('assets/img/logowhite.png') }}" style="background-color: var(--red-icot); padding: 13px;">

        <div class="versionContainer">
            <hr>
            <label class="lblVersion"> Versión {{ env('VERSION_WEB') }} </label>
        </div>

    </div>
    <div class="sidebar-background"></div>
</div>

<style>
    #userData {
        font-weight: 900;
    }

    hr {
        margin-left: 16px;
        margin-right: 16px;
    }

    .lblVersion {
        bottom: 50px;
        width: 100%;
        color: var(--red-icot);
        text-align: center !important;
        font-weight: 900;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        vertical-align: bottom;
    }

    .versionContainer {
        flex-grow: 1;
        display: flex;
        justify-content: flex-end;
        flex-direction: column;
    }
</style>