<?php
/*
 * This file is part of the PommProject/ModelManager package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\ModelManager\Model;

use PommProject\ModelManager\Exception\ModelException;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Session;

/**
 * ModelPooler
 *
 * Client pooler for model package.
 *
 * @package ModelManager
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class ModelPooler extends ClientPooler
{
    /**
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'model';
    }

    /**
     * The ModelPooler checks if the Session's ClientHolder has got
     * the wanted instance. If so, it is returned. Otherwise, it checks if the
     * wanted model class exists and try to instance it. It is then
     * registered in the ClientHolder and sent back.
     *
     * @throw ModelException if class can not be loaded or does not implement
     * the ClientInterface.
     * @see ClientPoolerInterface
     */
    public function getClient($class)
    {
        $class   = trim($class, '\\');
        $model = $this->session->getClient('model', $class);

        if ($model === null) {
            $model = $this->createModel($class);
            $this->session->registerClient($model);
        }

        return $model;
    }

    /**
     * createModel
     *
     * Model instance builder.
     * A ModelException is thrown if the class does not exist, does not
     * implement ClientInterface or is not a child of Model.
     *
     * @access protected
     * @param  string    $class
     * @throw  ModelException if incorrect
     * @return Model
     */
    protected function createModel($class)
    {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ModelException(sprintf(
                "Could not instanciate Model class '%s'. (Reason: '%s').",
                $class,
                $e->getMessage()
            ));
        }

        if (!$reflection->implementsInterface('\PommProject\Foundation\Client\ClientInterface')) {
            throw new ModelException(sprintf("'%s' class does not implement the ClientInterface interface.", $class));
        }

        if (!$reflection->isSubClassOf('\PommProject\ModelManager\Model\Model')) {
            throw new ModelException(sprintf("'%s' class does not extend \PommProject\ModelManager\Model.", $class));
        }

        return new $class();
    }
}
