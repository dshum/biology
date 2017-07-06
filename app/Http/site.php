<?php

use Moonlight\Main\Site;
use Moonlight\Main\Item;
use Moonlight\Properties\BaseProperty;
use Moonlight\Properties\CheckboxProperty;
use Moonlight\Properties\DatetimeProperty;
use Moonlight\Properties\DateProperty;
use Moonlight\Properties\FloatProperty;
use Moonlight\Properties\ImageProperty;
use Moonlight\Properties\IntegerProperty;
use Moonlight\Properties\OneToOneProperty;
use Moonlight\Properties\ManyToManyProperty;
use Moonlight\Properties\PasswordProperty;
use Moonlight\Properties\RichtextProperty;
use Moonlight\Properties\TextareaProperty;
use Moonlight\Properties\TextfieldProperty;
use Moonlight\Properties\VirtualProperty;

$site = \App::make('site');

$site->
    
    /*
	 * Раздел сайта
	 */

	addItem(
		Item::create('App\Section')->
		setTitle('Раздел сайта')->
		setMainProperty('name')->
		setRoot(true)->
        setTree(true)->
        setCreate(true)->
		setElementPermissions(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			TextfieldProperty::create('url')->
			setTitle('Адрес страницы')->
            setRequired(true)->
			addRule('regex:/^[a-z0-9\-]+$/i', 'Допускаются латинские буквы, цифры и дефис.')
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('h1')->
			setTitle('H1')->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('meta_keywords')->
			setTitle('META Keywords')->
			setShow(true)
		)->
		addProperty(
			TextareaProperty::create('meta_description')->
			setTitle('META Description')->
			setShow(true)
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Текст раздела')
		)->
        addProperty(
			OneToOneProperty::create('section_id')->
			setTitle('Раздел сайта')->
			setRelatedClass('App\Section')->
			setParent(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Служебный раздел
	 */

	addItem(
		Item::create('App\ServiceSection')->
		setTitle('Служебный раздел')->
		setMainProperty('name')->
		setRoot(true)->
        setTree(true)->
        setCreate(true)->
		setElementPermissions(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('App\ServiceSection')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Настройки сайта
	 */

	addItem(
		Item::create('App\SiteSettings')->
		setTitle('Настройки сайта')->
		setMainProperty('name')->
		setRoot(true)->
        setTree(true)->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')->
			setRequired(true)->
			setShow(true)
		)->
        addProperty(
			TextfieldProperty::create('meta_keywords')->
			setTitle('META Keywords')
		)->
		addProperty(
			TextareaProperty::create('meta_description')->
			setTitle('META Description')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->
    
    /*
	 * Ученик
	 */

	addItem(
		Item::create('App\User')->
		setTitle('Ученик')->
		setMainProperty('email')->
        setCreate(true)->
        setPerPage(10)->
        addOrderBy('created_at', 'desc')->
		addProperty(
			TextfieldProperty::create('email')->
			setTitle('E-mail')->
			setRequired(true)
		)->
        addProperty(
			TextfieldProperty::create('first_name')->
			setTitle('Имя')->
			setRequired(true)->
			setShow(true)
		)->
        addProperty(
			TextfieldProperty::create('last_name')->
			setTitle('Фамилия')->
			setRequired(true)->
			setShow(true)
		)->
        addProperty(
			TextfieldProperty::create('password')->
			setTitle('Пароль')->
			setHidden(true)
		)->
        addProperty(
			CheckboxProperty::create('activated')->
			setTitle('Активирован')
		)->
        addProperty(
			CheckboxProperty::create('banned')->
			setTitle('Заблокирован')
		)->
        addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('App\ServiceSection')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Предмет
	 */

	addItem(
		Item::create('App\Subject')->
		setTitle('Предмет')->
		setMainProperty('name')->
        setTree(true)->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			CheckboxProperty::create('hidden')->
			setTitle('Скрыто')
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('App\ServiceSection')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Тема
	 */

	addItem(
		Item::create('App\Topic')->
		setTitle('Тема')->
		setMainProperty('name')->
        setTree(true)->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			CheckboxProperty::create('hidden')->
			setTitle('Скрыто')
		)->
		addProperty(
			OneToOneProperty::create('subject_id')->
			setTitle('Предмет')->
			setRelatedClass('App\Subject')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Подтема
	 */

	addItem(
		Item::create('App\Subtopic')->
		setTitle('Подтема')->
		setMainProperty('name')->
        setTree(true)->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			CheckboxProperty::create('hidden')->
			setTitle('Скрыто')
		)->
		addProperty(
			OneToOneProperty::create('topic_id')->
			setTitle('Тема')->
			setRelatedClass('App\Topic')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Тест
	 */

	addItem(
		Item::create('App\Test')->
		setTitle('Тест')->
		setMainProperty('name')->
        setTree(true)->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			OneToOneProperty::create('topic_id')->
			setTitle('Тема')->
			setRelatedClass('App\Topic')->
			setParent(true)->
            setOpenItem(true)
		)->
		addProperty(
			OneToOneProperty::create('subtopic_id')->
			setTitle('Подтема')->
			setRelatedClass('App\Subtopic')->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Вопрос
	 */

	addItem(
		Item::create('App\Question')->
		setTitle('Вопрос')->
		setMainProperty('name')->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			RichtextProperty::create('question')->
			setTitle('Текст вопроса')->
			setShow(true)
		)->
		addProperty(
			RichtextProperty::create('explanation')->
			setTitle('Объяснение')
		)->
		addProperty(
			TextareaProperty::create('comments')->
			setTitle('Комментарий')
		)->
		addProperty(
			IntegerProperty::create('mark')->
			setTitle('Баллы')
		)->
		addProperty(
			TextfieldProperty::create('answer')->
			setTitle('Ответ')
		)->
		addProperty(
			ManyToManyProperty::create('tests')->
			setTitle('Тесты')->
			setRelatedClass('App\Test')->
            setRelatedMethod('questions')->
            setOpenItem(true)->
			setShow(true)
		)->
		addProperty(
			OneToOneProperty::create('topic_id')->
			setTitle('Тема')->
			setRelatedClass('App\Topic')->
			setParent(true)
		)->
		addProperty(
			OneToOneProperty::create('question_type_id')->
			setTitle('Тип вопроса')->
			setRelatedClass('App\QuestionType')->
			setShow(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Ответ
	 */

	addItem(
		Item::create('App\Answer')->
		setTitle('Ответ')->
		setMainProperty('name')->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			TextfieldProperty::create('answer')->
			setTitle('Ответ')->
			setShow(true)
		)->
		addProperty(
			CheckboxProperty::create('correct')->
			setTitle('Правильный')->
			setShow(true)
		)->
		addProperty(
			ManyToManyProperty::create('questions')->
			setTitle('Вопросы')->
			setRelatedClass('App\Question')->
            setRelatedMethod('tests')
		)->
		addProperty(
			OneToOneProperty::create('question_id')->
			setTitle('Вопрос')->
			setRelatedClass('App\Question')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Тест ученика
	 */

	addItem(
		Item::create('App\UserTest')->
		setTitle('Тест')->
		setMainProperty('name')->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			CheckboxProperty::create('complete')->
			setTitle('Завершен')
		)->
		addProperty(
			OneToOneProperty::create('user_id')->
			setTitle('Ученик')->
			setRelatedClass('App\User')->
			setParent(true)->
            setOpenItem(true)
		)->
		addProperty(
			OneToOneProperty::create('test_id')->
			setTitle('Тест')->
			setRelatedClass('App\Test')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Вопрос ученика
	 */

	addItem(
		Item::create('App\UserQuestion')->
		setTitle('Вопрос ученика')->
		setMainProperty('name')->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			CheckboxProperty::create('correct')->
			setTitle('Правильный')
		)->
		addProperty(
			TextfieldProperty::create('answer')->
			setTitle('Ответ')
		)->
		addProperty(
			OneToOneProperty::create('user_test_id')->
			setTitle('Тест ученика')->
			setRelatedClass('App\UserTest')->
			setParent(true)->
            setOpenItem(true)
		)->
		addProperty(
			OneToOneProperty::create('question_id')->
			setTitle('Вопрос')->
			setRelatedClass('App\Question')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Ответ ученика
	 */

	addItem(
		Item::create('App\UserAnswer')->
		setTitle('Ответ ученика')->
		setMainProperty('name')->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			OneToOneProperty::create('user_question_id')->
			setTitle('Вопрос ученика')->
			setRelatedClass('App\UserQuestion')->
			setParent(true)->
            setOpenItem(true)
		)->
		addProperty(
			OneToOneProperty::create('answer_id')->
			setTitle('Ответ')->
			setRelatedClass('App\Answer')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Тип вопроса
	 */

	addItem(
		Item::create('App\QuestionType')->
		setTitle('Тип вопроса')->
		setMainProperty('name')->
        setCreate(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('App\ServiceSection')->
			setParent(true)->
            setOpenItem(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	bind(Site::ROOT, ['App.Section', 'App.ServiceSection', 'App.SiteSettings'])->
	bind(env('site.subjects', 'App.ServiceSection.2'), 'App.Subject')->
    bind(env('site.dicts', 'App.ServiceSection.3'), 'App.ServiceSection')->
	bind(env('site.types', 'App.ServiceSection.5'), 'App.QuestionType')->
	bind(env('site.students', 'App.ServiceSection.1'), 'App.User')->
	bind('App.Subject', 'App.Topic')->
	bind('App.Topic', ['App.Subtopic', 'App.Test'])->
	bind('App.Subtopic', 'App.Test')->
	bind('App.Test', 'App.Question')->
	bind('App.Question', 'App.Answer')->

	end();