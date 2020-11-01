<?php
/** @var \WEEEOpen\Tarallo\User $user */
/** @var \WEEEOpen\Tarallo\Product $product */
/** @var bool $editing */
/** @var string $self */
$features = $product->getFeatures();
$brandModel = $this->e($product->getBrand()) . ' ' . $this->e($product->getModel());
$maybeVariant = rtrim(' ' . $this->e($product->getVariantOrEmpty()));

$this->layout(
	'main',
	[
		'title' => $brandModel . $maybeVariant,
		'user' => $user,
	]
);

$summary = \WEEEOpen\Tarallo\SSRv1\Summary\Summary::peel($product);
$summary_escaped = array_map([$this, 'e'], explode(', ', $summary));
unset($summary);

$bmv_rawurlencoded = $this->e(rawurlencode($product->getBrand()) . '/' . rawurlencode($product->getModel()) . '/' . rawurlencode($product->getVariant()));
$here = rtrim($self, '/') . '/';
$copyQuery = http_build_query([
	'copy-brand' => $product->getBrand(),
	'copy-model' => $product->getModel(),
	'copy-variant' => $product->getVariant(),
], null, '&', PHP_QUERY_RFC3986);
?>

<article class="container item root <?=$editing ? ' head editing' : ''?>" data-brand="<?=$this->e($product->getBrand())?>" data-model="<?=$this->e($product->getModel())?>" data-variant="<?=$this->e($product->getVariant())?>">
	<header class="row">
		<h2 class="col-12" id="code-<?=$this->e($product->getBrand())?>-<?=$this->e($product->getModel())?>-<?=$this->e($product->getVariant())?>"><?= $brandModel ?><small><?= $maybeVariant ?></small></h2>
	</header>

	<nav class="itembuttons row mx-md-2 mt-md-2">
		<?php if($editing): ?>
			<button class="btn btn-outline-primary btn-item col-4 col-sm-auto mr-auto cancel" role="button">
				<i class="fa fa-arrow-circle-left"></i>&nbsp;Cancel
			</button>
			<button class="btn btn-outline-primary btn-item col-4 col-sm-auto save" role="button">
				<i class="fa fa-save"></i>&nbsp;Save
			</button>
			<button class="btn btn-outline-danger btn-item col-4 col-sm-auto removenew" role="button" disabled>
				<i class="fa fa-trash"></i>&nbsp;Delete
			</button>
		<?php else: ?>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" role="button" href="<?= $here ?>edit?from=<?= $this->e(rawurlencode($here)) ?>">
				<i class="fa fa-cogs"></i>&nbsp;Edit
			</a>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" role="button" href="/new/product?<?= $this->e($copyQuery) ?>">
				<i class="fa fa-object-group"></i>&nbsp;Copy
			</a>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" data-toggle="collapse" href="#collapsible-features-product" role="button" aria-expanded="false" aria-controls="#collapsible-features-product">
				<i class="fa fa-globe"></i>&nbsp;Details
			</a>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" role="button" href="/new/item?<?= $this->e($copyQuery) ?>">
				<i class="fa fa-cube"></i>&nbsp;Create Item
			</a>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" role="button" href="/product/<?= $bmv_rawurlencoded ?>/items">
				<i class="fa fa-cubes"></i>&nbsp;Items
			</a>
			<a class="btn btn-outline-primary btn-item col-6 col-sm-4 col-md-auto" role="button" href="/product/<?= $bmv_rawurlencoded ?>/history">
				<i class="fa fa-users"></i>&nbsp;History
			</a>
		<?php endif ?>
	</nav>

	<?php if(count($summary_escaped) > 0 && !$editing): ?>
		<section class="summary open">
			<span><?= implode('<span class="sep">, </span></span><span>', $summary_escaped) ?></span>
		</section>
	<?php endif; ?>

	<?php if($editing): ?>
		<section class="own features editing">
			<?php $this->insert('featuresEdit', ['features' => $features]); ?>
		</section>

		<section class="addfeatures">
			<label>Feature:
				<select class="allfeatures">
				</select></label><button>Add</button>
		</section>
	<?php else: ?>
		<section class="features collapse" id="collapsible-features-product">
			<?php $this->insert('features', ['features' => $features]) ?>
		</section>
	<?php endif ?>
</article>
<?php if($editing): ?>
	<script>const activate = true;</script>
	<?php $this->insert('editor');
endif;
?>