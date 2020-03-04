<?php


namespace NovemBit\wp\plugins\UniversalPatcher\patcher;


use NovemBit\wp\plugins\UniversalPatcher\Main;

class Patch
{

    private $id;

    private $root_path;

    private $path;

    private $replace_map;

    private $backup_directory;

    private $backup_file_path;

    public function __construct(array $params = [])
    {
        $this->root_path = ABSPATH;

        foreach ($params as $key => $param) {
            $this->{$key} = $param;
        }

        if (!$this->backup_directory) {
            $this->backup_directory = WP_CONTENT_DIR . '/universal-patcher/backups';

            if (!file_exists($this->backup_directory)) {
                mkdir($this->backup_directory, 0777, true);
            }
        }

        $this->path = $this->root_path . $this->path;

        $this->id = md5($this->path);

        $this->backup_file_path = $this->backup_directory . '/' . $this->id;

        $this->validate();
    }

    private function validate()
    {

        if (!file_exists($this->path) || !is_file($this->path)) {
            return false;
        }

        return true;
    }

    private function getPatchOptionName()
    {
        return Main::instance()->plugin_name . '-patch-' . $this->id;
    }

    private function setStatus($status)
    {
        return update_option($this->getPatchOptionName(), $status);
    }

    public function isApplied()
    {
        return get_option($this->getPatchOptionName(), false);
    }


    /**
     * @return bool
     */
    public function apply()
    {
        if (!$this->validate() || !$this->backup() || $this->isApplied()) {
            return false;
        }

        /**
         * Getting backup file content
         * */
        $content = file_get_contents($this->backup_file_path);

        foreach ($this->replace_map as $item) {
            $pattern = $item['pattern'] ?? '';
            $replace = $item['replace'] ?? '';
            $content = preg_replace($pattern, $replace, $content);
        }

        if (file_put_contents($this->path, $content)) {
            $this->setStatus(true);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function restore()
    {
        if (!$this->validate() || !$this->isApplied()) {
            return false;
        }

        return $this->revert() && $this->setStatus(false);
    }

    /**
     * @return bool
     */
    private function revert()
    {
        return copy($this->backup_file_path, $this->path) || unlink($this->backup_file_path);
    }

    /**
     * @return bool
     */
    private function backup()
    {
        return copy($this->path, $this->backup_file_path);
    }
}