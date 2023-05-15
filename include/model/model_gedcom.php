<?php

require __DIR__ . '/../database_function.php';

class model_gedcom extends database_function
{
	/**
	 * Generate new GEDCOM number for item (person, family, source, repo, address, etc.) only numerical, like: 1234
	 * 
	 *	$sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
	 *	$sql = "SELECT fam_gedcomnumber  FROM humo_families WHERE pers_tree_id=:tree_id";
	 */
	function generate_gedcomnr(int $tree_id, string $item): int
	{
		$new_gedcomnumber = 0;

		if ($item == 'person') {
			$sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
		}
		if ($item == 'family') {
			$sql = "SELECT fam_gedcomnumber FROM humo_families WHERE fam_tree_id=:tree_id";
		}
		if ($item == 'source') {
			$sql = "SELECT source_gedcomnr FROM humo_sources WHERE source_tree_id=:tree_id";
		}
		if ($item == 'address') {
			$sql = "SELECT address_gedcomnr FROM humo_addresses WHERE address_tree_id=:tree_id";
		}
		if ($item == 'repo') {
			$sql = "SELECT repo_gedcomnr FROM humo_repositories WHERE repo_tree_id=:tree_id";
		}
		if ($item == 'text') {
			$sql = "SELECT text_gedcomnr FROM humo_texts WHERE text_tree_id=:tree_id";
		}
		if ($item == 'event') {
			$sql = "SELECT event_gedcomnr FROM humo_events WHERE event_tree_id=:tree_id";
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':tree_id' => $tree_id
		]);
		$rows = $stmt->fetch(PDO::FETCH_OBJ);

		foreach ($rows as $row) {
			$gednum = $this->alphaNumStringGedcomToNumeric($row->text_gedcomnr);
			if ($gednum > $new_gedcomnumber) {
				$new_gedcomnumber = $gednum;
			}
		}

		$new_gedcomnumber++;
		return $new_gedcomnumber;
	}

	/**
	 * AlphaNum String Gedcomnumber To Numeric
	 * 
	 * Removes all non-digit characters (including spaces etc.) for all kinds of GEDCOM numbers like I1234, 1234I, U1234, X1234.
	 */
	private function alphaNumStringGedcomToNumeric(string $gedcomNumber): int
	{
		return (int)(preg_replace('/\D/', '', $gedcomNumber));
	}
}
