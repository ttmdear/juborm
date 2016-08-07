<?php
namespace Juborm\Assistant;

use Juborm\ORM;

class Template extends ORM
{
    private $template;
    private $binded = array();

    public static function factory($file)
    {
        $file = __DIR__."/templates/$file.php";
        $assert = static::service('assert');
        $assert->isReadable($file);

        $template = new static();
        $template->setTemplate(file_get_contents($file));

        return $template;
    }

    public function bind($placeholder, $value)
    {
        if (is_string($value)) {
            $this->template = str_replace("#$placeholder", $value, $this->template);
        }elseif(is_array($value)){
            $this->template = str_replace("#$placeholder", $this->exportArray($value), $this->template);
        }

        $this->binded[] = $placeholder;

        return $this;
    }

    private function exportArray($array)
    {
        $isVector = true;

        foreach (array_keys($array) as $value) {
            if (!is_numeric($value)) {
                $isVector = false;
                break;
            }
        }

        if ($isVector) {
            $string = "array(";

            foreach ($array as $value) {
                $string .= "'$value',";
            }

            $string .= ")";
        }else{
            $string = "array(\n";

            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $value = $this->exportArray($value);
                    $string .= "        '$key' => $value,\n";
                }else{
                    $string .= "        '$key' => '$value',\n";
                }

            }

            $string .= "    )";
        }

        return $string;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function __toString()
    {
        return $this->proccess($this->template);
    }

    public function toString()
    {
        return $this->__toString();
    }

    public function save($path)
    {
        $assert = $this->service('assert');
        $status = file_put_contents($path, $this->toString());

        if ($status === false) {
            $assert->exception("Can't write file $path.");
        }

        return $this;
    }

    private function proccess($template)
    {
        $proccessed = "";
        $lines = explode("\n", $template);
        $count = count($lines);

        $concat = true;
        for ($i=0; $i < $count; $i++) {
            $line = $lines[$i];
            $command = $this->command($line);

            if ($command != null) {
                switch ($command) {
                case 'else':
                    if (!$concat) {
                        $concat = true;
                    }

                    break;
                case 'fi':
                    $concat = true;
                    break;
                default:
                    if (in_array($command, $this->binded)) {
                        $concat = true;
                    }else{
                        $concat = false;
                    }

                    break;
                }

                continue;
            }

            if ($concat) {
                $proccessed .= "$line\n";
            }
        }

        return $proccessed;
    }

    private function command($line)
    {
        $reg = "/\\s*# if (\\w*).*/";
        preg_match($reg, $line, $matches);

        if (!empty($matches)) {
            return $matches[1];
        }

        $reg = "/\\s*# else/";

        preg_match($reg, $line, $matches);
        if (!empty($matches)) {
            return "else";
        }

        $reg = "/\\s*# fi/";

        preg_match($reg, $line, $matches);
        if (!empty($matches)) {
            return "fi";
        }

        return null;
    }
}
