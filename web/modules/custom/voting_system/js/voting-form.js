(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.votingForm = {
    attach: function (context, settings) {
      once('voting-form', '.voting-option', context).forEach(function (element) {
        const $element = $(element);
        const $radio = $element.find('input[type="radio"]');

        // Adicionar classe selected se o radio estiver marcado
        if ($radio.is(':checked')) {
          $element.addClass('selected');
        }

        // Adicionar evento de clique
        $element.on('click', function (e) {
          // Remover classe selected de todas as opções
          $('.voting-option').removeClass('selected');
          // Adicionar classe selected na opção clicada
          $element.addClass('selected');
          // Marcar o radio button
          $radio.prop('checked', true).trigger('change');
        });
      });
    }
  };
})(jQuery, Drupal); 