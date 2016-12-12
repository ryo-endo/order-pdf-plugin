<?php
/*
 * This file is part of the Order Pdf plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\OrderPdf;

use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    private $logoName = 'logo.png';

    /**
     * @var string
     */
    private $logoPath;

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        $this->logoPath = __DIR__.'/Resource/template/'.$this->logoName;
    }

    /**
     * Install.
     *
     * @param array       $config
     * @param Application $app
     */
    public function install($config, $app)
    {
        // Backup logo.
        $this->copyLogo($app['config'], $config['code']);
    }

    /**
     * Uninstall.
     *
     * @param array       $config
     * @param Application $app
     */
    public function uninstall($config, $app)
    {
        // Remove temp
        $this->removeLogo($app['config'], $config['code']);

        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * Enable.
     *
     * @param array       $config
     * @param Application $app
     */
    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * Disable.
     *
     * @param array       $config
     * @param Application $app
     */
    public function disable($config, $app)
    {
    }

    /**
     * Update.
     *
     * @param array       $config
     * @param Application $app
     */
    public function update($config, $app)
    {
        $this->copyLogo($app['config'], $config['code']);

        // Update
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * Backup logo before update.
     *
     * @param array  $config
     * @param string $pluginCode
     */
    private function copyLogo($config, $pluginCode)
    {
        $src = $this->getPluginTemplateDir().'/'.$this->logoName;
        $target = $this->getAppTemplateDir($config).'/'.$pluginCode.'/'.$this->logoName;

        // コピー先にすでにファイルが存在する場合は、ユーザーが変更したロゴ画像を残すために上書きをしない
        if (file_exists($target) || !file_exists($src)) {
            return;
        }

        $file = new Filesystem();
        $file->copy($src, $target, true);
    }

    /**
     * Remove logo.
     *
     * @param array  $config
     * @param string $pluginCode
     */
    private function removeLogo($config, $pluginCode)
    {
        $target = $this->getAppTemplateDir($config).'/'.$pluginCode.'/'.$this->logoName;

        if (!file_exists($target)) {
            return;
        }

        $file = new Filesystem();
        $file->remove($target);
    }

    /**
     * Plugin内のテンプレートディレクトリのパスを取得する.
     *
     * @return string
     */
    private function getPluginTemplateDir()
    {
        return __DIR__.'/Resource/template';
    }

    /**
     * app/template内のテンプレートディレクトリのパスを取得する.
     *
     * @param array $config
     *
     * @return string
     */
    private function getAppTemplateDir($config)
    {
        return $config['template_realdir'].'/../admin';
    }
}
