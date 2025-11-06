$(document).ready(function () {
  $('#contact').on('submit', function (e) {
    e.preventDefault();

    // Clear only dynamic error messages
    $('.field-error').remove();
    $('#mail_success, #mail_failed, #error_email, #error_message').hide();

    $.ajax({
      type: 'POST',
      url: '../php/send_message_email.php',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          $('#modal-message').text(response.message || 'Your message has been sent!');
          $('#contactModal').css('display', 'block');
          $('#contact')[0].reset();
        } else {
          // field-level errors
          if (response.errors && response.errors.name) {
            $('#name').after('<span class="field-error error">' + response.errors.name + '</span>');
          }
          if (response.errors && response.errors.email) {
            $('#email').after('<span class="field-error error">' + response.errors.email + '</span>');
          }
          if (response.errors && response.errors.message) {
            $('#message').after('<span class="field-error error">' + response.errors.message + '</span>');
          }

          // top-level message if any
          if (response.message) {
            $('#mail_failed').text(response.message).show();
          } else {
            $('#mail_failed').text('Error, email not sent').show();
          }
        }
      },
      error: function (xhr, status, error) {
        console.log('Error:', error);
        $('#mail_failed').text('An error occurred. Please try again.').show();
      }
    });
  });

  // Modal close
  $(document).on('click', '#contactModal .close', function () {
    $('#contactModal').css('display', 'none');
  });
  $(window).on('click', function (event) {
    if ($(event.target).is('#contactModal')) {
      $('#contactModal').css('display', 'none');
    }
  });
});
