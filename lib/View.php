<?php namespace lib;

class View
{
    private $template;
    private $data = array();

    public function __construct($template, array $data) {
        if (!file_exists('../views/'.$template.'.secret')) throw new \Exception('File '.$template.'.secret not found!');

        $this->template = file_get_contents('../views/'.$template.'.secret');
        $this->data = $data;
    }

    public function loadParentTemplates() {
        $this->template = preg_replace_callback('#@extends\((.*?)\)#is', array($this, 'loadCallback'), $this->template);
    }

    public function loadCallback($matches) {
        return file_get_contents('../views/'.str_replace(['"', "'"], '', $matches[1]).'.secret');
    }

    public function sections() {
        preg_replace_callback('#@section\((.*?)\)(.*?)@stop#is', array($this, 'sectionCallback'), $this->template);
    }

    public function sectionCallback($matches) {
        $yield = '@yield('.$matches[1].')';
        if (strpos($this->template, $yield) !== false) {
            $this->template = str_replace($yield, trim($matches[2]), $this->template);
            $this->template = str_replace($matches[0], '', $this->template);
            return '';
        } else {
            throw new \Exception('Yield '.$matches[1].' is not declared in template file!');
            return '';
        }
    }

    public function removeUnusedYields() {
        $this->template = preg_replace('#@yield\((.*?)\)#is', '', $this->template);
    }

    public function functionsCallback($matches) {
        $output = '';
        $explode = explode(' ', $matches[1]);
        preg_match_all('#{(.*?)}#is', $matches[2], $replace_matches);

        if (strpos($explode[0], '()') !== false) {
            $skip = true;
            $e = explode('->', $explode[0]);
            $key = str_replace('()', '', $e[1]);
            $foreach = $this->data[$e[0]]->$key();
        } else {
            if (!array_key_exists($explode[0], $this->data)) {
                throw new \Exception('Undefined variable $'.$explode[0]);
            }

            $foreach = $this->data[$explode[0]];
        }

        if (!array_key_exists($explode[0], $this->data) && !$skip) {
            throw new \Exception('Undefined variable $'.$explode[0]);
        }

        // there might be a multiple variables inside a loop
        $search = [];
        foreach ($replace_matches[1] as $key => $match) {
            if (strpos($match, '->') !== false) {
                if (strpos($match, '()') !== false) {
                    $explode2 = explode('->', $match);
                    if (count($explode2) == 2) {
                        $name = str_replace(['"', "'", '->', $explode[2], '()'], '', $match);
                        $search[] = array('search' => '{'.$match.'}', 'name' => $name);
                        $type[] = 4;
                    } else {
                        $name = str_replace('()', '', $explode2[1]);
                        $name2 = str_replace('()', '', $explode2[2]);
                        $search[] = array('search' => '{'.$match.'}', 'name' => $name, 'name2' => $name2);
                        $type[] = 5;
                    }
                } else {
                    $name = str_replace(['"', "'", '->', $explode[2]], '', $match);
                    $search[] = array('search' => '{'.$match.'}', 'name' => $name);
                    $type[] = 1;
                }
            } elseif (strpos($match, '[') !== false) {
                $name = str_replace(['"', "'", '[', ']', $explode[2]], '', $match);
                $search[] = array('search' => '{'.$match.'}', 'name' => $name);
                $type[] = 2;
            } else {
                $search[] = array('search' => '{'.$match.'}');
                $type[] = 3;
            }
        }

        foreach ($foreach as $item) {
            $add = $matches[2];
            foreach ($search as $key => $s) {
                if ($type[$key] == 1) {
                    $add = str_replace($s['search'], $item->$s['name'], $add);
                } elseif ($type[$key] == 2) {
                    $add = str_replace($s['search'], $item[$s['name']], $add);
                } elseif ($type[$key] == 3) {
                    $add = str_replace($s['search'], $item, $add);
                } elseif ($type[$key] == 4) {
                    $add = str_replace($s['search'], $item->$s['name'](), $add);
                } elseif ($type[$key] == 5) {
                    $add = str_replace($s['search'], $item->$s['name']()->$s['name2'], $add);
                }
            }

            $output .= $add;
        }

        return $output;
    }

    public function parseFunctions() {
        $this->template = preg_replace_callback('#{loop (.*?)\}(.*?){/loop}#is', array($this, 'functionsCallback'), $this->template);
    }

    private function variablesCallback($matches) {
        $match = str_replace(['{', '}'], '', $matches[0]);
        foreach ($this->data as $key => $value) {
            if ($key == $match || (strpos($match, $key) !== false && strpos($match, '[') !== false) || (strpos($match, $key) !== false && strpos($match, '->') !== false)) {
                if (strpos($match, '->') !== false) {
                    $explode = explode('->', $match);
                    if (strpos($explode[1], '()') !== false) {
                        $explode = str_replace('()', '', $explode[1]);
                        return $value->$explode();
                    } else {
                        $output = $value->$explode[1];
                        if (isset($output)) {
                            return $value->$explode[1];
                        } else {
                            throw new \Exception('Undefined property $'.$match);
                        }
                    }
                } elseif (strpos($match, '[') !== false) {
                    $explode = explode('[', $match);
                    $parse = str_replace([']', '"', "'"], '', $explode[1]);

                    if (array_key_exists($parse, $value)) {
                        return $value[$parse];
                    } else {
                        return '';
                    }
                } else {
                    if (is_object($value)) {
                        return $value->response(true);
                    }
                    return $value;
                }
            } else {
                if (defined($match)) {
                    return constant($match);
                }
            }

        }

        throw new \Exception('Undefined variable $'.$match);
    }

    private function parseVariables() {
        $this->template = preg_replace_callback('#{(.*?)}#is', array($this, 'variablesCallback'), $this->template);
    }

    public function conditionsCallback($matches) {
        if (strpos($matches[1], ' ') !== false) {
            $parse = explode(' ', $matches[1]);

            // if (!array_key_exists($parse[0], $this->data)) {
                // return '';
            // } else {
                if ($parse[1] == '==') {
                    if (strpos($parse[0], '->') !== false) {
                        if (substr($parse[0], -2) == '[]') {
                            $first = explode('->', substr($parse[0], 0, -2));
                            $first_item = $this->data[$first[0]]->$first[1]();
                        } else {
                            $first = explode('->', $parse[0]);
                            $first_item = $this->data[$first[0]]->$first[1];
                        }


                        if (strpos($parse[2], '->') !== false) {
                            $second = explode('->', $parse[2]);
                            $second_item = $this->data[$second[0]]->$second[1];
                            if ($first_item == $second_item) {
                                return $matches[2];
                            }
                        } else {

                            if ($first_item == $parse[2]) {
                                return $matches[2];
                            }
                        }
                    } else {
                        if ($this->data[$parse[0]] == $parse[2] || $this->data[$parse[0]] == intval($parse[2])) {
                            return $matches[2];
                        }
                    }
                } elseif ($parse[1] == '>=') {
                    if ($this->data[$parse[0]] >= intval($parse[2])) {
                        return $matches[2];
                    }
                } elseif ($parse[1] == '<=') {
                    if ($this->data[$parse[0]] <= intval($parse[2])) {
                        return $matches[2];
                    }
                } elseif ($parse[1] == '!=') {
                    if ($this->data[$parse[0]] != $parse[2] || $this->data[$parse[0]] != intval($parse[2])) {
                        return $matches[2];
                    }
                }
            // }
        } else if (!array_key_exists($matches[1], $this->data)) {
            return '';
        } elseif (count($this->data[$matches[1]]) == 0) {
            return '';
        } else {
            return $matches[2];
        }
    }

    private function elseConditionsCallback($matches) {
        if (strpos($matches[1], ' ') !== false) {

            $parse = explode(' ', $matches[1]);
            
            // if (!array_key_exists($parse[0], $this->data)) {
                // return '';
            // } else {
                if ($parse[1] == '==') {
                    if (strpos($parse[0], '->') !== false) {
                        if (substr($parse[0], -2) == '[]') {
                            $first = explode('->', substr($parse[0], 0, -2));
                            $first_item = $this->data[$first[0]]->$first[1]();
                        } else {
                            $first = explode('->', $parse[0]);
                            $first_item = $this->data[$first[0]]->$first[1];
                        }


                        if (strpos($parse[2], '->') !== false) {

                            $second = explode('->', $parse[2]);
                            $second_item = $this->data[$second[0]]->$second[1];
                            if ($first_item == $second_item) {
                                return $matches[2];
                            } else {
                                return $matches[3];
                            }
                        } else {

                            if ($first_item == $parse[2]) {
                                return $matches[2];
                            } else {
                                return $matches[3];
                            }
                        }
                    } else {
                        if ($this->data[$parse[0]] == $parse[2] || $this->data[$parse[0]] == intval($parse[2])) {
                            return $matches[2];
                        } else {
                            return $matches[3];
                        }
                    }
                } elseif ($parse[1] == '>=') {
                    if ($this->data[$parse[0]] >= intval($parse[2])) {
                        return $matches[2];
                    }else {
                        return $matches[3];
                    }
                } elseif ($parse[1] == '<=') {
                    if ($this->data[$parse[0]] <= intval($parse[2])) {
                        return $matches[2];
                    }else {
                        return $matches[3];
                    }
                } elseif ($parse[1] == '!=') {
                    if ($this->data[$parse[0]] != $parse[2] || $this->data[$parse[0]] != intval($parse[2])) {
                        return $matches[2];
                    }else {
                        return $matches[3];
                    }
                }
            // }
        } else if (!array_key_exists($matches[1], $this->data)) {
            return $matches[3];
        } elseif (count($this->data[$matches[1]]) == 0) {
            return $matches[3];
        } else {
            return $matches[2];
        }
    }

    private function parseConditions() {
        $this->template = preg_replace_callback('#@if\((.*?)\)(.*?)@endif#is', array($this, 'conditionsCallback'), $this->template);
    }

    private function parseFirstConditions() {
        $this->template = preg_replace_callback('#@fif\((.*?)\)(.*?)@fendif#is', array($this, 'conditionsCallback'), $this->template);
    }


    private function parseElseConditions() {
        $this->template = preg_replace_callback('#@if\((.*?)\)(.*?)@else(.*?)@endif#is', array($this, 'elseConditionsCallback'), $this->template);
    }

    private function parseInsideElseConditions() {
        $this->template = preg_replace_callback('#@iif\((.*?)\)(.*?)@ielse(.*?)@iendif#is', array($this, 'elseConditionsCallback'), $this->template);
    }

    public function render() {
        while(strpos($this->template, '@extends') !== false) {
            $this->loadParentTemplates();
        }

        $this->sections();
        $this->removeUnusedYields();

        $this->parseFirstConditions();

        $this->parseInsideElseConditions();
        $this->parseElseConditions();

        
        $this->parseConditions();
        $this->parseFunctions();
        $this->parseVariables();

        return $this->template;
    }
}