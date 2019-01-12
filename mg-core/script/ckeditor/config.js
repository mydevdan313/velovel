//Внимание! в файле ckeditor.js заменено вхождение подстроки if("pre") в строчке 275  на if("1"), чтобы при открытии исходного кода, редактор не вырезал переносы строк \n
CKEDITOR.editorConfig = function (config) {

    var site = admin.SITE.replace(/http(s)?:\/\//, '');
    config.filebrowserUploadUrl = site + '/ajax?mguniqueurl=action/upload';
    config.toolbarGroups = [
        {name: 'saveContent'},
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'links' },
        { name: 'colors' },
        { name: 'insert' },  
        { name: 'others' },
        { name: 'forms' }, 
        { name: 'tools' }, 
        { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },       
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        { name: 'styles' },
   
        { name: 'about' }
    ];

    
    config.removeButtons = 'CodeSnippet,Subscript,Superscript,Cut,Copy,Paste,PasteText,PasteFromWord,Anchor,Outdent,Indent,Styles,Source,CommentSelectedRange,autoFormat,UncommentSelectedRange,AutoComplete';
    config.extraPlugins = 'ajaxsave';
    // правила для исключения обработки тегов в визуальном режиме
    config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
    config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);
    config.protectedSource.push(/<\?[\s\S]*?\?>/g);
    config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);
    config.protectedSource.push(/<(a|p)[^>]*>.*<\/$1>/ig);
    //config.protectedSource.push(/[\r\n]+/ig);
    
    config.contentsCss = ['../mg-core/script/ckeditor/plugins/spoiler/css/spoiler.css', '../mg-core/script/ckeditor/contents.css'];

    config.enterMode = CKEDITOR.ENTER_BR; // при переносе строки подставит br, а не <p>&nbsp</p> 
    config.allowedContent = true;
        config.entities = false;
    config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre';
    config.removeDialogTabs = 'image:advanced;link:advanced';

    // настройки codemirror
    config.codemirror = {
        theme: 'default',
        lineNumbers: true,
        lineWrapping: false,
        matchBrackets: true,
        autoCloseTags: true,
        autoCloseBrackets: false,
        enableSearchTools: true,
        enableCodeFolding: false,
        enableCodeFormatting: false,
        autoFormatOnStart: false,
        autoFormatOnUncomment: false,
        highlightActiveLine: false,
        highlightMatches: false,
        showTrailingSpace: false,
        showFormatButton: false,
        showCommentButton: false,
        showUncommentButton: false
    };

};

CKEDITOR.on('instanceReady', function (ev) {
    var blockTags = ['div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'li', 'blockquote', 'ul', 'ol', 'table', 'thead', 'tbody', 'tfoot', 'td', 'th', ];
    // не применять форматирование к html тегам
    var rules = {
        indent: true,
        breakBeforeOpen: true,
        breakAfterOpen: true,
        breakBeforeClose: true,
        breakAfterClose: true
    };

    for (var i = 0; i < blockTags.length; i++) {
        ev.editor.dataProcessor.writer.setRules(blockTags[i], rules);
    }

});

// метод вызова файлового менеджера
CKEDITOR.on('dialogDefinition', function (event) {
    var editor = event.editor;
    var dialogDefinition = event.data.definition;
    var dialogName = event.data.name;

    if (dialogName == 'image' || dialogName == 'link') {
        var tabCount = dialogDefinition.contents.length;
        for (var i = 0; i < tabCount; i++) {
            var browseButton = dialogDefinition.contents[i].get('browse');
            if (browseButton !== null) {
                browseButton.hidden = false;
                browseButton.onClick = function (dialog, i) {
                    editor._.filebrowserSe = this;
                    // передаем номер отложенной функции для обработки полученного файла из файлового менеджера
                    admin.openUploader('uploader.getFileCallbackCKEDITOR', editor._.filebrowserFn);
                    // $('.cke_dialog').css('z-index', '90'); 
                }
            }
        }
    }

    // при закрытии диалога HTML кода, показываем панель редактора, т.к. она теряет фокус и исчезает
    if (dialogName == 'sourcedialog') {

        dialogDefinition.dialog.on('cancel', function (cancelEvent) {
            $('#' + 'cke_' + editor.name).show();
        }, this, null, -1);

        dialogDefinition.dialog.on('ok', function (cancelEvent) {
            $('#' + 'cke_' + editor.name).show();
        }, this, null, -1);

        dialogDefinition.dialog.on('show', function (cancelEvent) {
            $('.cke_dialog_contents_body').css({
                padding: '0px',
                height: 'auto'
            });
            $('.cke_dialog_ui_vbox_child').css('padding', '0px');

        }, this, null, -1);

        dialogDefinition.contents[0].elements[0].style = 'display:none;';
        dialogDefinition.contents[0].elements[1].style = 'display:none;';
        var width = 601.6666666666666;

        if($(window).height()<600){
			width = 400;
        }

        dialogDefinition.contents[0].elements[2].style = 'cursor:auto;width:800px;height:'+width+'px;tab-size:4;text-align:left;';

    }

});
