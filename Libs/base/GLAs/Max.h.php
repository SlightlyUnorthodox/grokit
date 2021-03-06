<?
// Copyright 2014 Tera Insights, LLC. All Rights Reserved

function Max( array $t_args, array $input, array $output ) {
    grokit_assert( \count($output) >= 1,
        'Max GLA  produces at least one output!');
    grokit_assert( \count($output) == \count($input),
        'Max GLA should have the same number of inputs and outputs');

    $nValues = \count($output);

    $inputNames = array_keys($input);
    $outputNames = array_keys($output);

    // Outputs should be the same type as the inputs
    for($index = 0; $index < $nValues; $index++) {
        array_set_index($output, $index, array_get_index($input, $index));
    }

    $name = generate_name('Max_');
?>
class <?=$name?> {
    uintmax_t count;

<? foreach($output as $k => $v) { ?>
    <?=$v?> _<?=$k?>;
<?  } // foreach output ?>

public:
    <?=$name?>() :
<?  foreach($output as $k => $v) { ?>
        _<?=$k?>(),
<?  } // foreach output ?>
        count(0)
    { }

    void AddItem( <?=const_typed_ref_args($input)?> ) {
        if( count > 0 ) {
<?  for($index = 0; $index < $nValues; $index++) { ?>
            _<?=$outputNames[$index]?> = std::max(_<?=$outputNames[$index]?>, <?=$inputNames[$index]?>);
<?  } // foreach value ?>
        } else {
<?  for($index = 0; $index < $nValues; $index++) { ?>
            _<?=$outputNames[$index]?> = <?=$inputNames[$index]?>;            
<?  } // foreach value ?>
        }

        count++;
    }
    void AddState( <?=$name?> & o ) {
        if (count > 0 && o.count > 0) {
<?  for($index = 0; $index < $nValues; $index++) { ?>
            _<?=$outputNames[$index]?> = std::max(_<?=$outputNames[$index]?>, o._<?=$outputNames[$index]?>);
<?  } // foreach value ?>
        } else if(o.count > 0) { // count == 0
<?  for($index = 0; $index < $nValues; $index++) { ?>
            _<?=$outputNames[$index]?> = o._<?=$outputNames[$index]?>;            
<?  } // foreach value ?>
        }
        // Otherwise, count > 0 && o.count == 0, so just keep our values

        count += o.count;
    }

    void GetResult(<?=typed_ref_args($output)?>) {
<?  foreach($output as $k => $v) { ?>
        <?=$k?> = _<?=$k?>;
<?  } // foreach output ?>
    }
};
<?
    return [
        'kind'        => 'GLA',
        'name'        => $name,
        'input'       => $input,
        'output'      => $output,
        'result_type' => 'single',
        'system_headers' => [ 'algorithm', 'cstdint' ],
        ];
}
?>
