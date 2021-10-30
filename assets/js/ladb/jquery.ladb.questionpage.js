+function ($) {
    'use strict';

    // CLASS DEFINITION
    // ======================

    var LadbQuestionPage = function(element, options) {
        this.options = options;
        this.$element = $(element);

        this.$answers = $('.ladb-qa-question-answers', this.$element);

        this.$btnNewAnswer = $('#ladb_answer_btn', this.$element);

        this.$hiddenRow = null;
        this.$answerForm = null;

    };

    LadbQuestionPage.DEFAULTS = {
        answerNewPath: null,
        answerEditPath: null
    };

    LadbQuestionPage.prototype.removeAnswerForm = function() {
        if (this.$answerForm) {
            this.$answerForm.remove();
            this.$answerForm = null;
        }
    };

    LadbQuestionPage.prototype.revealHiddenRow = function() {
        if (this.$hiddenRow) {
            this.$hiddenRow.show();
            this.$hiddenRow = null;
        }
    };

    LadbQuestionPage.prototype.bindAnswerRow = function($row) {
        var that = this;

        // Bind buttons
        $('.ladb-btn-edit', $row).on('click', function(e) {
            e.preventDefault();
            $(this).blur();

            var editPath = $(this).attr('href');

            // Load edit answer form
            $.ajax(editPath, {
                cache: false,
                dataType: "html",
                context: document.body,
                success: function(data, textStatus, jqXHR) {
                    that.bindEditAnswerBox($row, data);
                },
                error: function () {
                    console.log('error');
                }
            });

        });
        $('.ladb-btn-thanks', $row).on('click', function(e) {
            e.preventDefault();
            $(this).blur();

            var thanksMsg = $(this).data('thanks-msg');

            // Write comment with thanks msg
            var $commentWidget = $('.ladb-comment-widget', $row);
            $commentWidget.ladbCommentWidget('writeComment', thanksMsg);

        });

        $('.ladb-comment-widget', $row).ladbCommentWidget();
        LADBCommon.setupTooltips();
        LADBCommon.setupPopovers();

    };

    LadbQuestionPage.prototype.bindEditAnswerBox = function($row, data) {
        var that = this;

        this.removeAnswerForm();
        this.revealHiddenRow();
        this.$btnNewAnswer.show();

        this.$answerForm = $(data);

        $row.hide();
        $row.after(this.$answerForm);
        this.$hiddenRow = $row;

        // Bind collection
        $("[data-form-widget=collection]", this.$answerForm).each(function () {
            new window.infinite.Collection(this, $('[data-prototype]', $(this).next()));
        });

        // Bind form
        var $form = $('form', this.$answerForm).first();
        $form.ajaxForm({
            cache: false,
            dataType: "html",
            context: document.body,
            clearForm: true,
            success: function(data, textStatus, jqXHR) {
                that.removeAnswerForm();
                if ($(data).hasClass('ladb-answer-row')) {
                    var $newRow = $(data);
                    $row.replaceWith($newRow);
                    that.$hiddenRow = null;
                    that.bindAnswerRow($newRow);
                    LADBCommon.lazyLoadReset($newRow);
                } else {
                    that.bindEditAnswerBox($row, data);
                }
            },
            error: function() {
                console.log('error');
            }
        });

        // Bind buttons
        $('.ladb-btn-cancel', this.$answerForm).on('click', function() {
            that.removeAnswerForm();
            that.revealHiddenRow();
            return false;
        });
        $('.ladb-btn-submit', this.$answerForm).on('click', function() {
            $(this).button('loading');
            $form.submit();
        });

        // Focus the first textarea
        $('textarea', $form).first().focus();

    };

    LadbQuestionPage.prototype.bindNewAnswerBox = function(data) {
        var that = this;

        this.removeAnswerForm();
        this.revealHiddenRow();

        this.$answerForm = $(data);

        this.$btnNewAnswer.hide();
        this.$btnNewAnswer.button('reset');
        $('.ladb-answers-footer').append(this.$answerForm);

        // Bind collection
        $("[data-form-widget=collection]", this.$answerForm).each(function () {
            new window.infinite.Collection(this, $('[data-prototype]', $(this).next()));
        });

        // Bind form
        var $form = $('form', this.$answerForm).first();
        $form.ajaxForm({
            cache: false,
            dataType: "html",
            context: document.body,
            clearForm: true,
            success: function(data, textStatus, jqXHR) {
                if ($(data).hasClass('ladb-success')) {
                    var $row = $('.ladb-answer-row', data);
                    var $header = $('.ladb-answers-header', data);
                    var $footer = $('.ladb-answers-footer', data);
                    that.$answers.append($row);
                    $('.ladb-answers-header', that.$element).replaceWith($header);
                    $('.ladb-answers-footer', that.$element).replaceWith($footer);
                    that.removeAnswerForm();
                    that.bindAnswerRow($row);
                    that.bindSorters();
                    LADBCommon.lazyLoadReset($row);
                } else {
                    that.bindNewAnswerBox(data);
                }
            },
            error: function() {
                console.log('error');
            }
        });

        // Bind buttons
        $('.ladb-btn-cancel', this.$answerForm).on('click', function() {
            that.removeAnswerForm();
            that.$btnNewAnswer.show();
            return false;
        });
        $('.ladb-btn-submit', this.$answerForm).on('click', function() {
            $(this).button('loading');
            $form.submit();
        });

        // ScrollTo form
        this.$answerForm.ladbScrollTo(null, {
            onAfter: function() {

                LADBCommon.setupTextareas();

                // Focus the first textarea
                $('textarea', $form).first().focus();

            }
        });

    };

    LadbQuestionPage.prototype.bindSorters = function() {
        var that = this;

        // Bind sorters
        $('.ladb-sorter-item', this.$element).on('click', function(e) {
            e.preventDefault();

            var url = $(this).attr("href");

            // Fake loading
            $('.ladb-qa-question-answers').addClass('ladb-translucent');
            $('.ladb-sorter-btn', that.$element).button('loading');

            // Load answers list
            $.ajax(url, {
                cache: false,
                dataType: "html",
                context: document.body,
                success: function(data, textStatus, jqXHR) {
                    var $answers = $('.ladb-qa-question-answers', data);
                    var $header = $('.ladb-answers-header', data);
                    $('.ladb-qa-question-answers', that.$element).replaceWith($answers);
                    $('.ladb-answers-header', that.$element).replaceWith($header);
                    that.bindSorters();
                    that.bindRows();
                    LADBCommon.lazyLoadReset($answers);
                },
                error: function () {
                    console.log('error');
                }
            });

        });

    };

    LadbQuestionPage.prototype.bindRows = function() {
        var that = this;

        // Bind rows
        $('.ladb-answer-row', this.$element).each(function(index, value) {
            that.bindAnswerRow($(value));
        });

    };

    LadbQuestionPage.prototype.bind = function() {
        var that = this;

        this.bindSorters();
        this.bindRows();

        // Bind buttons
        this.$btnNewAnswer.on('click', function() {
            $(this).button('loading');

            // Load new answer form
            $.ajax(that.options.answerNewPath, {
                cache: false,
                dataType: "html",
                context: document.body,
                success: function(data, textStatus, jqXHR) {
                    that.bindNewAnswerBox(data);
                },
                error: function () {
                    console.log('error');
                }
            });

        });
        $('.ladb-answers-footer .ladb-btn-thanks', this.$element).on('click', function(e) {
            e.preventDefault();
            $(this).blur();

            var thanksMsg = $(this).data('thanks-msg');

            // Write comment with thanks msg
            var $commentWidget = $('.ladb-comment-widget', $('.ladb-qa-question')).first();
            $commentWidget.ladbCommentWidget('writeComment', thanksMsg);

        });

    };

    LadbQuestionPage.prototype.init = function() {
        var that = this;

        this.bind();
        LADBCommon.setupPopovers();

    };


    // PLUGIN DEFINITION
    // =======================

    function Plugin(option) {
        return this.each(function () {
            var $this   = $(this);
            var data    = $this.data('ladb.questionpage');
            var options = $.extend({}, LadbQuestionPage.DEFAULTS, $this.data(), typeof option == 'object' && option);

            if (!data) {
                $this.data('ladb.questionpage', (data = new LadbQuestionPage(this, options)));
            }
            if (typeof option == 'string') {
                data[option]();
            } else {
                data.init();
            }
        })
    }

    var old = $.fn.ladbQuestionPage;

    $.fn.ladbQuestionPage             = Plugin;
    $.fn.ladbQuestionPage.Constructor = LadbQuestionPage;


    // NO CONFLICT
    // =================

    $.fn.ladbQuestionPage.noConflict = function () {
        $.fn.ladbQuestionPage = old;
        return this;
    }

}(jQuery);