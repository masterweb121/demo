<?php
/**
 * This file is part of Vegas package
 *
 * @author Frank Broersen <frank@pitgroup.nl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * Component render service
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Page\Services;

class Component extends \Vegas\DI\Service\ComponentAbstract
{
    protected function setUp($params = array())
    {        
        if(is_array($params)) {
            $position = $params[0];
            $level    = isset($params[1]) ? $params[1] : 'PAGE';
        } else {
            $position = (int)$params;
            $level    = 'PAGE';
        }
        
        $config = isset($params[2]) ? $params[2] : array();
        
        $session = $this->di->get('session');
        $mode = 'view';
        if($session->has('mode'))
            $mode = $session->get('mode');
        
        $page_id = $this->di->get('page')->_id;
        
        $components = \Page\Models\Component::find(array(array(
            "page_id"   => $page_id,
            "level"     => $level,
            "position"  => $position,
            "updated_at" => array('$gt' => 0)   // only display updated components, clean these up
        ),'sort' => array('rank' => 1)));
                
        foreach($components as $i => $component) {                        
            $renderer = new \Vegas\DI\Component\Renderer($this->di->get('view'));
            $renderer = $renderer->setMode($mode)
                                 ->setModuleName($component->module)
                                 ->setTemplateName(strtolower($component->class) . '/view');
            $class = "\\{$component->module}\\Components\\{$component->class}";
            $components[$i]->instance = new $class($renderer); 
        }
        
        return array(
            'allowed'    => isset($config['allow'])  ? $config['allow'] : false,
            'blocked'    => isset($config['block'])  ? $config['block'] : false,
            'limit'      => isset($config['limit'])  ? $config['limit'] : 0,
            'forced'     => isset($config['forced']) ? $config['forced'] : false,
            'position'   => $position,
            'level'      => $level,
            'mode'       => $mode,
            'components' => $components,
        );
    }
}