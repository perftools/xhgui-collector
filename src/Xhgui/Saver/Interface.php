<?php

/**
 * Interface for all savers
 */
interface Xhgui_Saver_Interface
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function save(array $data);
}
