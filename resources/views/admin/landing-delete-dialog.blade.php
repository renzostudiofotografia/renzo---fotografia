<dialog id="landing-delete-dialog" class="landing-delete-dialog" aria-labelledby="landing-delete-title" aria-describedby="landing-delete-description">
    <span class="eyebrow">GESTIONAR CAMPAÑA</span><h2 id="landing-delete-title">¿Eliminar esta landing?</h2>
    <p class="landing-delete-name" data-delete-landing-name></p>
    <p id="landing-delete-description">La landing dejará de ser pública y se moverá a la papelera. Sus contactos se conservarán. Puedes restaurarla como borrador.</p>
    <p class="landing-delete-error" data-delete-landing-error role="status" hidden></p>
    <form id="landing-delete-form" method="POST">@csrf @method('DELETE')<input type="hidden" name="revision" value=""><input type="hidden" name="confirmed" value="1"><div class="landing-delete-actions"><button class="button secondary" type="button" data-cancel-landing-delete autofocus>Cancelar</button><button class="button landing-delete-confirm" type="submit">Mover a papelera</button></div></form>
</dialog>
