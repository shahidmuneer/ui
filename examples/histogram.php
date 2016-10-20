<?php
use UI\Window;
use UI\Point;
use UI\Size;
use UI\Box;
use UI\Spin;
use UI\ColorButton;
use UI\Button;
use UI\Area;
use UI\DrawPath;
use UI\DrawBrush;
use UI\Draw;
use UI\DrawStroke;
use UI\DrawMatrix;

$window = new Window("libui Histogram Example", new Size(640, 480), true);

$window->setMargin(true);

$hBox = new Box(BOX::HORIZONTAL);
$hBox->setPadded(true);

$vBox = new Box(BOX::VERTICAL);
$vBox->setPadded(true);

$window->add($hBox);

$hBox->append($vBox);

$dataSources = [];

function getGraphPoints(array $dataSources, Size $size) : array {
	$width = $size->getWidth();
	$height = $size->getHeight();

	$xincr = $width / 9;
	$yincr = $height / 100;

	$points = [];

	foreach ($dataSources as $i => $source) {
		$points[$i] = new Point(
						$xincr * $i, 
						$yincr * (100 - $source->getValue()));
	}

	return $points;
}

function getGraphPath(array $locations, Size $size, bool $extend = false) : DrawPath {
	$path = new DrawPath(DRAWPATH::WINDING);

	foreach ($locations as $location) {
		$path->lineTo($location);
	}

	if ($extend) {
		$path->lineTo(new Point(
						$size->getWidth(), 
						$size->getHeight()));
		$path->lineTo(new Point(
						0, 
						$size->getHeight()));
		$path->closeFigure();
	}

	$path->end();
	return $path;
}

$colorButton = new ColorButton();

$white = new DrawBrush(DRAWBRUSH::SOLID, 1, 1, 1, 1);
$black = new DrawBrush(DRAWBRUSH::SOLID, 0, 0, 0, 1);

$histogram = new Area();

$histogram->onDraw(function($area, $context, $areaSize, $clipPoint, $clipSize) use($white, $black, &$dataSources, $colorButton) {
	$path = new DrawPath(DRAWPATH::WINDING);

	$path
		->addRectangle($clipPoint, $areaSize);

	$path->end();

	Draw::fill($context, $path, $white);

	$graphSize = new Size(
		$areaSize->getWidth() - 40, 
		$areaSize->getHeight() - 40);

	$path = new DrawPath(DRAWPATH::WINDING);

	$zero = new Point(20, 20);

	$path->newFigure($zero);

	$path->lineTo(new Point(
		20, 
		20 + $graphSize->getHeight()));

	$path->lineTo(new Point(
		20 + $graphSize->getWidth(), 
		20 + $graphSize->getHeight()));
	
	$path->end();

	$stroke = new DrawStroke(DRAWSTROKE::CAP_FLAT, DRAWSTROKE::JOIN_MITER, 2, 10);

	Draw::stroke($context, $path, $black, $stroke);

	$matrix = new DrawMatrix();
	$matrix->translate($zero);

	Draw::transform($context, $matrix);

	$points = 
		getGraphPoints($dataSources, $graphSize);

	$path = getGraphPath($points, $graphSize, true);

	$brush = $colorButton->getBrush();
	
	Draw::fill($context, $path, $brush);

	$path = getGraphPath($points, $graphSize, false);

	$brush->setAlpha(
		$brush->getAlpha()/2);

	Draw::stroke($context, $path, $brush, $stroke);
});

$histogram->onMouse(function($area, $areaPoint, $areaSize, $flags){
	if ($flags & AREA::DOWN) {
		printf("Mouse down %d x %d\n", $areaPoint->getX(), $areaPoint->getY());
	}

	if ($flags & AREA::UP) {
		printf("Mouse up %d x %d\n", $areaPoint->getX(), $areaPoint->getY());
	}
});

$redrawHistogram = function() use($histogram) {
	$histogram->redraw();
};

$brush = new DrawBrush(DRAWBRUSH::SOLID);
$brush->setAlpha(1);
$brush->setRGB(0x8892BF); # this is the color of PHP, internally ...

$colorButton->setColorFromBrush($brush);
$colorButton->onChange($redrawHistogram);

for ($i = 0; $i < 10; $i++) {
	$dataSources[$i] = new Spin(0, 100);
	$dataSources[$i]->setValue(mt_rand(0, 100));
	$dataSources[$i]->onChange($redrawHistogram);
	$vBox->append($dataSources[$i]);
}

$vBox->append($colorButton);

$hBox->append($histogram, true);

$window->show();

UI\main();
?>
