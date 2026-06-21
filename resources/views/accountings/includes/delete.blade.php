<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                <h4 class="my-0">¿Estás seguro de eliminar esto?</h4>
                <p>Todos los datos relacionados con esto pueden eliminarse</p>
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="row justify-content-center mt-20">
                        <div class="col-sm-12 col-md-5 mb-2">
                            <button type="submit" class="btn btn-danger w-100">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-5">
                            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
