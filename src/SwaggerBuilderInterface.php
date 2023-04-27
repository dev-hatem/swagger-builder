<?php

namespace Creatify\SwaggerBuilder;

interface SwaggerBuilderInterface
{
    public function generatePagination();
    public function generateIndex();
    public function generateShow();
    public function generateStore();
    public function generateUpdate();
    public function generateDelete();
    public function generateRestore();
    public function generateForceDelete();
}
