<?php

declare(strict_types=1);

namespace UchiPro\Courses;

class LessonType
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public static function create($id, $title): self
    {
        $lesson = new self();
        $lesson->id = $id;
        $lesson->title = $title;
        return $lesson;
    }

    public static function createLecture(): self
    {
        $lesson = new self();
        $lesson->id = 'lecture';
        $lesson->title = 'Лекция';
        return $lesson;
    }

    public static function createQuiz(): self
    {
        $lesson = new self();
        $lesson->id = 'quiz';
        $lesson->title = 'Тестирование';
        return $lesson;
    }

    public static function createEssay(): self
    {
        $lesson = new self();
        $lesson->id = 'essay';
        $lesson->title = 'Практическое задание';
        return $lesson;
    }

    public static function createScorm(): self
    {
        $lesson = new self();
        $lesson->id = 'scorm';
        $lesson->title = 'Встраиваемый интерактивный урок';
        return $lesson;
    }
}
