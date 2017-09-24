<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */

	class cms_link_model extends Banshee\model {
		private $columns = array("text", "link");

		public function count_links() {
			$query = "select count(*) as count from links";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_links($offset, $limit) {
			if (isset($_SESSION["link_order"]) == false) {
				$_SESSION["link_order"] = array("text", "link");
			}

			if ((in_array($_GET["order"], $this->columns)) && ($_GET["order"] != $_SESSION["link_order"][0])) {
				$_SESSION["link_order"] = array($_GET["order"], $_SESSION["link_order"][0]);
			}

			$query = "select l.*, c.category from links l, link_categories c where l.category_id=c.id";

			$search = array();
			if ($_SESSION["link_search"] != null) {
				foreach ($this->columns as $i => $column) {
					$this->columns[$i] = $column." like %s";
					array_push($search, "%".$_SESSION["link_search"]."%");
				}
				$query .= " having (".implode(" or ", $this->columns).")";
			}

			$query .= " order by %S,%S limit %d,%d";

			return $this->db->execute($query, $search, $_SESSION["link_order"], $offset, $limit);
		}

		public function get_categories() {
			$query = "select * from link_categories order by category";

			return $this->db->execute($query);
		}

		public function get_link($link_id) {
			return $this->db->entry("links", $link_id);
		}

		public function save_oke($link) {
			$result = true;

			if (trim($link["text"]) == "") {
				$this->view->add_message("Enter the link text.");
				$result = false;
			}

			list($proto) = explode("://", $link["link"], 2);
			if (in_array($proto, array("http", "https")) == false) {
				$this->view->add_message("Enter a valid link.");
				$result = false;
			}

			return $result;
		}

		public function create_link($link) {
			$keys = array("id", "category_id", "text", "link");

			$link["id"] = null;

			return $this->db->insert("links", $link, $keys);
		}

		public function update_link($link) {
			$keys = array("category_id", "text", "link");

			return $this->db->update("links", $link["id"], $link, $keys);
		}

		public function delete_oke($link) {
			$result = true;

			return $result;
		}

		public function delete_link($link_id) {
			return $this->db->delete("links", $link_id);
		}
	}
?>
