<script>
  const generateCustomTheme = () => {
    const customTheme = @json($customTheme);

    const primary_color = customTheme.primary_color;
    const secondary_color = customTheme.secondary_color;
    const accent_color = customTheme.accent_color;

    const formInputCustom = document.querySelectorAll('.form-input-custom');
    formInputCustom.forEach(element => {
      element.style.backgroundColor = secondary_color ?? '#ffffff';
      element.style.color = accent_color ?? '#607080';
      element.style.borderColor = primary_color ?? '#607080';
    });

    const fontCustom = document.querySelectorAll('.font-custom');
    fontCustom.forEach(element => {
      element.style.color = accent_color ?? '#607080';
    })

    const formSelectCustom = document.querySelectorAll('.form-select-custom');
    formSelectCustom.forEach(element => {
      element.style.backgroundColor = secondary_color ?? '#ffffff';
      element.style.color = accent_color ?? '#607080';
      element.style.borderColor = primary_color ?? '#607080';
    });

    const formCheckboxCustom = document.querySelectorAll('.form-checkbox-custom');
    formCheckboxCustom.forEach(element => {
      element.style.backgroundColor = secondary_color ?? '#ffffff';
      element.style.color = accent_color ?? '#607080';
      element.style.borderColor = primary_color ?? '#607080';
    });

    const fontCardCustom = document.querySelectorAll('.font-card-custom');
    fontCardCustom.forEach(element => {
      element.style.color = accent_color ?? '#ffffff';
    })

    const cardHeaderCustom = document.querySelectorAll('.card-header-custom');
    cardHeaderCustom.forEach(element => {
      element.style.backgroundColor = secondary_color ?? '#435ebe';
    })

    const cardBodyCustom = document.querySelectorAll('.card-body-custom');
    cardBodyCustom.forEach(element => {
      element.style.backgroundColor = primary_color ?? '#ffffff';
    })

    const btnPrimaryCustom = document.querySelectorAll('.btn-primary-custom');
    btnPrimaryCustom.forEach(element => {
      element.style.backgroundColor = secondary_color ?? '#435ebe';
      element.style.color = accent_color ?? '#ffffff';
      element.style.transition = 'background-color 0.3s ease, color 0.3s ease';

      element.addEventListener('mouseenter', () => {
        element.style.backgroundColor = accent_color ?? '#ffffff';
        element.style.color = primary_color ?? '#435ebe';
      });

      element.addEventListener('mouseleave', () => {
        element.style.backgroundColor = secondary_color ?? '#435ebe';
        element.style.color = accent_color ?? '#ffffff';
      });
    });

    const btnCloseCustom = document.querySelectorAll('.btn-close-custom');
    btnCloseCustom.forEach(element => {
      element.style.color = accent_color ?? '#000000';
    })

    const spinnerCustom = document.querySelectorAll('.spinner-custom');
    spinnerCustom.forEach(element => {
      element.style.color = accent_color ?? '#435ebe';
    })

    const modalContentCustom = document.querySelectorAll('.modal-content-custom');
    modalContentCustom.forEach(element => {
      element.style.backgroundColor = primary_color ?? '#ffffff';
    })

    const navTabsCustom = document.querySelectorAll('.nav-tabs-custom');
    navTabsCustom.forEach(element => {
      const isActive = element.classList.contains('active');
      element.style.backgroundColor = isActive ? (secondary_color ?? '#435ebe') : ('transparent');
      element.style.color = isActive ? (accent_color ?? '#ffffff') : (accent_color ?? '#435ebe');
    });

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        navLinks.forEach(nav => {
          nav.style.backgroundColor = 'transparent';
          nav.style.color = accent_color ?? '#435ebe';
        });
        link.style.backgroundColor = secondary_color ?? '#435ebe';
        link.style.color = '#ffffff';
      });
    });
  };

  const applyCustomStyles = () => {
    const themeColor = "{{ $customTheme->accent_color ?? '#607080' }}";
    const secondaryColor = "{{ $customTheme->secondary_color ?? '#ffffff' }}";

    $('.dataTables_filter input').css({
      'background-color': secondaryColor,
      'color': themeColor,
      'border-color': secondaryColor
    });

    $('.dataTables_length select').css({
      'background-color': secondaryColor,
      'color': themeColor,
      'border-color': secondaryColor
    });

    $('.dataTables_paginate .paginate_button a').css({
      'background-color': secondaryColor,
      'color': themeColor,
      'border-color': secondaryColor
    });

    $('.dataTables_info').css({
      'color': themeColor
    });

    $('.dataTables_empty').css({
      'color': themeColor,
    });
  }
</script>
