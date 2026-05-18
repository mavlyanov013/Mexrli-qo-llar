<?php

namespace App\Enums;

enum SectionType: string
{
    case HERO = 'hero';
    case VALUE = 'value';
    case TEAM = 'team';
    case DOC = 'doc';
    case LEGAL = 'legal';
}
