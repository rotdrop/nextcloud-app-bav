<?php

namespace OCA\BAV\Tests\Unit\Controller;

use OCP\IRequest;
use PHPUnit\Framework\TestCase;

use OCA\BAV\Controller\PageController;


class PageControllerTest extends TestCase {
	public function testIndexReturnsViewParameters(): void {
		$request = $this->createMock(IRequest::class);
		$controller = new PageController('bav', $request);

		$this->assertSame([], $controller->index());
	}

}
