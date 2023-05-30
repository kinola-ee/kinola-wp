<?php

namespace Kinola\KinolaWp\Api;

class Response {
	protected array $contents;

	public function __construct( array $contents ) {
		$this->contents = $contents;
	}

	public function get_data(): array {
		return $this->contents['data'];
	}

	public function has_next_link(): bool {
		return isset($this->contents['links']['next']) && $this->get_next_link();
	}

	public function get_next_link(): string {
		return $this->contents['links']['next'];
	}
}