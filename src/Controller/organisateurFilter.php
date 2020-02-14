<?php

function organisateur($sorties)
{
    if ($sorties->getId() % 2 == 0) {
        return true;
    } else {
        return false;
    }
}