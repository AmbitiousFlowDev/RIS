<?php

abstract class Controller
{
    private array $observers = [];

    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }
    protected function notify(string $event, $data = null): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }
    protected function render(string $view, array $data = [])
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require "views/$view.php";
        $html = ob_get_clean();

        if (class_exists('Security')) {
            $html = Security::prepareHtmlResponse($html);
        }

        echo $html;
    }
    protected function redirect(string $route)
    {
        header("Location: index.php?$route");
        exit;
    }
}