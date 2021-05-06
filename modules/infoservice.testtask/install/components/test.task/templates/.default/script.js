;(function() {
    var ajaxURL = document.location.origin + '/local/components/infoservice.testtask/test.task/ajax.php?action=';

    var selector = {
        testSelector: '.test-button',
    };
    var classList = {
        hidden: 'test-hidden',
        noReaction: 'test-no-reaction'
    };

    /**
     * Обработчик нажатия по тестовой ссылке
     *
     * @return void
     */
    var testAction = function() {
        $(this).addClass(classList.noReaction);

        $.post(ajaxURL + 'testaction', {}, answer => {
            $(this).removeClass(classList.noReaction);
            if(!answer.result) return;

            console.log(answer);
        });
    };

    /**
     * Обработчик готовности загрузки страницы
     *
     * @return void
     */
    var initPage = function() {
        console.log('ok');
    };

    $(document)
        .on('ready', initPage)
        .on('click', selector.testSelector, testAction)
    ;

})();