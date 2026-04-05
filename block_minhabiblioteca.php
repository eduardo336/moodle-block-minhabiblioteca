<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Minha Biblioteca block main class.
 *
 * @package    block_minhabiblioteca
 * @copyright  2026 UFMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_minhabiblioteca extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_minhabiblioteca');
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return ['all' => true];
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        global $USER, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = '';
            return $this->content;
        }

        // url com sesskey — proteção csrf
        $redirecturl = new moodle_url('/blocks/minhabiblioteca/redirect.php', [
            'sesskey' => sesskey(),
        ]);

        $label = get_string('accesslink', 'block_minhabiblioteca');

        $button = html_writer::link(
            $redirecturl,
            html_writer::tag('span', $label),
            [
                'class'  => 'btn btn-primary block-minhabiblioteca-btn',
                'title'  => $label,
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
            ]
        );

        // url estática visível só para quem pode gerenciar atividades (professores/admins)
        // verifica o contexto da página, não do bloco, para herança correta
        $pagecontext = $this->page->context;
        $editinfo = '';
        /* url para professores — para reativar, remova as marcações de comentário
        if ($this->page->user_is_editing() && has_capability('moodle/course:manageactivities', $pagecontext)) {
            $gourl = (new moodle_url('/blocks/minhabiblioteca/go.php'))->out(false);
            $editinfo = html_writer::div(
                html_writer::tag('input', '', [
                    'type'     => 'text',
                    'class'    => 'block-minhabiblioteca-url form-control form-control-sm',
                    'value'    => $gourl,
                    'readonly' => 'readonly',
                    'onclick'  => 'this.select();',
                    'title'    => $gourl,
                ]),
                'block-minhabiblioteca-editinfo mt-2'
            );
        }
        */

        /* imagem clicável — para ativar, remova as marcações de comentário
         * coloque o arquivo em: blocks/minhabiblioteca/pix/banner.png (260x120px recomendado)
        $imgurl = new moodle_url('/blocks/minhabiblioteca/pix/banner.png');
        $banner = html_writer::link(
            $redirecturl,
            html_writer::empty_tag('img', [
                'src'   => $imgurl,
                'alt'   => $label,
                'title' => $label,
                'class' => 'block-minhabiblioteca-banner',
            ]),
            [
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
            ]
        );
        */

        $this->content->text = html_writer::div(
            $button . /* $banner . */ $editinfo,
            'block-minhabiblioteca-wrapper'
        );

        return $this->content;
    }
}
