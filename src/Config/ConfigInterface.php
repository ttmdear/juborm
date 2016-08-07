<?php
namespace Juborm\Config;

interface ConfigInterface
{
    /**
     * This method return a part of config base on path.
     * <example>
     *     $config->get('models.bookstore.dir');
     * </example>
     *
     * The above example should return dir config to specific model.
     *
     * @param string $path For example sources.bookstore.adapter
     * @return string|array
     */
    public function get($path);
}
