/**
 * Модуль для обработки ошибок запроса.
 */

var errors = (function() {
  // тут будет обработка ошибок возращаемых в ajaxReuest
  return {
    noneReport: '',
    //Показывает всплывающее окно с текстом ошибки
    showErrorBlock: function (errorText) {
      var errorDescHide = '';

      if (errorText.length == 0) {
        errorDescHide = 'style="display:none;"';
      }

      var infoText = errors.getErrorText(errorText);
      var errorBox = "" +
        "<div class='error-box'>" +
        "<a href='javascript:void(0)' class='close-notification link text-right' onclick='$(\".error-box\").remove()' style='float:right;'>"+lang.CLOSE+"</a>" +
        "<div class='sorry-error'>" + lang.SORRY_ERROR + "<br>" +
        "<!--<a href='https://moguta.ru/feedback' "+errors.noneReport+">" + lang.TECHNICAL_SUPPORT + "</a>-->" +
        "</div><div class='text-error'><div class='description'></div>" +
        "<a href='javascript:void(0);' onclick='errors.openError()'"+errorDescHide+">" + lang.TECHNICAL_ERROR_DESCRIPTION + "</a>" +
        "<div class='original' style='display: none'>" + errorText + "</div></div>" +
        "<a href='javascript:void(0);' "+errors.noneReport+" class='link send-report-btn' style='color:#fff;float:right;margin-top:5px;' onClick='admin.downimg()' >" +
        "<span>"+lang.SEND_REPORT+"</span></a><div class='clear'></div></div>";
      $('.error-box').remove();
      $('body').append(errorBox);
      admin.centerPosition($('.error-box'));
      $('.error-box .text-error .description').html(infoText);
    },

    openError: function () {
      $('.error-box .text-error .original').toggle();
    },

    //Возвращает текст для всплывающего окна, основываясь на тесте полученной ошибки
    getErrorText: function (errorText) {
      lang.SORRY_ERROR = lang.ERRORS_MESSAGE_1;

      if (/The requested URL could not be retrieved/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_2;
        errors.noneReport = 'style="display:none;"';
      } else if (/Connection timed out/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_3;
        errors.noneReport = 'style="display:none;"';
      } else if (/You have an error in your SQL syntax/.test(errorText)) {
        lang.SORRY_ERROR = lang.ERRORS_MESSAGE_4;
        errorText = lang.ERRORS_MESSAGE_5;
      } else if (/server has gone away/.test(errorText)) {
        lang.SORRY_ERROR = lang.ERRORS_MESSAGE_4;
        errorText = lang.ERRORS_MESSAGE_7;
      }  else if (/error 28 from table/.test(errorText)) {
        lang.SORRY_ERROR = lang.ERRORS_MESSAGE_1;
        errorText = lang.ERRORS_MESSAGE_8;
        errors.noneReport = 'style="display:none;"';
      }  else if (/Bad Gateway/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_9;
        errors.noneReport = 'style="display:none;"';
      } else if (/Your request timed out/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_11;
        errors.noneReport = 'style="display:none;"';
      } else if (/Internal Server Error/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_12;
        errors.noneReport = 'style="display:none;"';
      } else if (/Access denied for user/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_13;
        errors.noneReport = 'style="display:none;"';
      } else if (/Access denied/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_14;
        errors.noneReport = 'style="display:none;"';
      } else if (/Can't connect to MySQL server on/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_15;
        errors.noneReport = 'style="display:none;"';
      } else if (/Proxy Error/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_16;
        errors.noneReport = 'style="display:none;"';
      } else if (/Unknown MySQL server host/.test(errorText)) {
        errorText = lang.ERRORS_MESSAGE_17;
        errors.noneReport = 'style="display:none;"';
      }

      if(errorText==''){
        errorText = lang.ERRORS_MESSAGE_18;
        errors.noneReport = 'style="display:none;"';
        lang.SORRY_ERROR = lang.ERRORS_MESSAGE_19;
      }

      return errorText;
    }
  }
}());