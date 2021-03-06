<?php
/*
 * Copyright 2017-2019 by Heiko Schäfer <heiko@rangun.de>
 *
 * This file is part of webvirus.
 *
 * webvirus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * webvirus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with webvirus.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'iformable.php';

final class Form implements IRenderable {

  private $body;
  private $html;

  function __construct(IFormable $body, $name = null) {
    $this->body = $body;
    $this->html = "<form ".(!is_null($name) ? "name=\"".$name."\" " : "")."method=\"".$body->method()."\"".
    (empty($body->action()) ? "" : " action=\"".$body->action()."\" ").
    (empty($body->encType()) ? "" : " enctype=\"".$body->enctype()."\"").">";
  }

  public function __toString() {

    foreach($this->body->hidden() as $k => $v) {
      $this->html .= "<input type=\"hidden\" name=\"".$k."\" value=\"".$v."\">";
    }

    $this->html .= $this->body->render()."</form>";

    return $this->html;
  }

  public function render() {
    return $this->__toString();
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
