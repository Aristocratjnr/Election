<?php
include 'configs/dbconnection.php';

if (isset($_GET['election_id']) && is_numeric($_GET['election_id'])) {
  $election_id = $_GET['election_id'];
} else {
  echo '<script>window.history.back();</script>';
  exit;
}

$row = $conn->prepare("SELECT * FROM election ORDER BY created_at DESC");
$row->execute();
$result = $row->get_result();
?>

<form id="add-category-form">
  <div class="modal-body">
    <div class="row g-3">
      <div class="col-md-12">
        <label for="category" class="form-label">Category Name</label>
        <input type="hidden" name="electionID" id="electionID" value="<?php echo $election_id; ?>">
        <input type="text" name="name" class="form-control" id="name" data-error-message="Category name is required">
        <div class="invalid-feedback"></div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="reset" class="btn btn-secondary">Reset</button>
    <button type="submit" class="btn btn-primary">Add Category</button>
  </div>
</form>

<script>
  $('#add-category-form').submit(function(e) {
    e.preventDefault()

    let isValid = true;
    $("#add-category-form").find("input").each(function() {
      const input = $(this);
      const value = input.val().trim();
      const errorMessage = input.data("error-message");
      if (value === "" && input.attr('name') !== 'electionID') {
        isValid = false;
        input.addClass("is-invalid");
        input.siblings(".invalid-feedback").text(errorMessage);
      } else {
        input.removeClass("is-invalid");
        input.siblings(".invalid-feedback").text("");
      }
    });

    if (isValid) {
      $.ajax({
        url: 'api/save_category_fixed.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          if (response.success) {
            $('#addCategory').modal('hide');
            toastr.success(response.message);
            setTimeout(function() {
              location.reload();
            }, 1500);
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr, status, error) {
          toastr.error('An error occurred while saving the category');
        }
      });
    }
  });

  // Real-time validation
  $("#add-category-form").find("input").on("input", function() {
    const input = $(this);
    const value = input.val().trim();
    const errorMessage = input.data("error-message");

    if (value === "" && input.attr('name') !== 'electionID') {
      input.addClass("is-invalid");
      input.siblings(".invalid-feedback").text(errorMessage);
    } else {
      input.removeClass("is-invalid");
      input.siblings(".invalid-feedback").text("");
    }
  });
</script>