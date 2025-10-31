
$(function(){
  // smooth scroll for horizontal rows on arrow click (optional)
  $('.more-link').on('click', function(e){
    // allow normal click to category page
  });
  // small animation for product cards on load
  $('.product-card').css({opacity:0, transform:'translateY(8px)'}).each(function(i){
    $(this).delay(i*80).animate({opacity:1, transform:'translateY(0)'}, 400);
  });
});

// Password visibility toggle
$(document).ready(function() {
    $('.password-toggle').on('click', function() {
        const $input = $(this).closest('.password-field').find('input');
        const $icon = $(this).find('i');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
        }
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Close alert button
    $('.alert .btn-close').on('click', function() {
        $(this).closest('.alert').remove();
    });
});