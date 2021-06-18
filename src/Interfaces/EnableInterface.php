<?php
/**
 * Created by PhpStorm.
 * User: vaz
 * Date: 6/19/15
 * Time: 5:59 PM
 */
namespace App\Interfaces;

/**
 * This interface use for active or inactive from admin page
 *
 * Interface ActiveInterface
 * @package AppBundle\Model
 */
interface EnableInterface
{
    public function setEnabled($isEnabled);
    public function getEnabled();
}