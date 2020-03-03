<?php


namespace NovemBit\wp\plugins\UniversalPatcher\patcher;


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

        $this->path = ABSPATH . $this->path;

    }

    private function validate()
    {

        if (!file_exists($this->path) || !is_file($this->path)) {
            return false;
        }

        $this->id = md5($this->path);

        $this->backup_file_path = $this->backup_directory . '/' . $this->id;

        return true;
    }

    public function apply()
    {
        if (!$this->validate() || !$this->backup()) {
            return false;
        }

        /**
         * Getting backup file content
         * */
        $content = file_get_contents($this->backup_file_path);

        foreach ($this->replace_map as $pattern => $replace) {
            $content = preg_replace($pattern, $replace, $content);
        }

        file_put_contents($this->path, $content);

        return true;
    }

    public function restore()
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->revert();
    }

    private function revert()
    {
        return copy($this->backup_file_path, $this->path);
    }

    private function backup()
    {
        return copy($this->path, $this->backup_file_path);
    }
}