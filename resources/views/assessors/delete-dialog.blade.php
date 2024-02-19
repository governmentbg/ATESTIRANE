<div class="modal fade" id="deleteEmplyeeModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fs-5"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Изтриване!</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center">
				Премахването на това <strong>досие</strong> ще премахне цялата информация за този служител !<br>
				Сигурни ли сте че искате да продължите?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
				<button type="button" class="btn btn-danger delete_submit">
					<span class="spinner-border spinner-border-sm d-none me-1" id="dialog_spinner" role="status" aria-hidden="true"></span>
					Изтриване
				</button>
			</div>
		</div>
	</div>
</div>