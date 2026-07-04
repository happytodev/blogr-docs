<?php

arch('forbid dd, dump, ray')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'exit', 'die'])
    ->not->toBeUsed();
