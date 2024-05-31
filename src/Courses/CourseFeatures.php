<?php

declare(strict_types=1);

namespace UchiPro\Courses;

class CourseFeatures
{
    /**
     * Используются слайды.
     */
    public bool $slides = false;

    /**
     * Используется видео.
     */
    public bool $video = false;

    /**
     * Есть тестирование.
     */
    public bool $testing = false;

    /**
     * Есть практические задания.
     */
    public bool $practice = false;

    /**
     * Есть интерактивные элементы.
     */
    public bool $interactive = false;
}
