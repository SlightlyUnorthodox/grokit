<?
// This GLA simply accumulates its inputs into a single vector to be used as a
// state. Each row of the input is placed into a tuple which is then appended to
// the end of the vector. Vectors are combined across different processes simply
// by concatenation with no particular order.

// Template Args:
// init.size: The initial capacity for the vector.
// use.array: If true, an array is used instead of a tuple. All inputs must have
//   the same type in such a case.
function Gather(array $t_args, array $inputs, array $outputs) {
    // Class name randomly generated
    $className = generate_name('Gather');

    // Initialization of local variables from template arguments.
    $initSize = get_default($t_args, 'init.size', 0);
    $useArray = get_default($t_args, 'use.array', false);

    if ($useArray) {
        $innerType = array_get_index($inputs, 0);
        foreach ($inputs as $type)
             grokit_assert($innerType == $type,
                           'Gather: array must contain equivalent types.');
        $numInputs = \count($inputs);
    }

    $outputs = array_combine(array_keys($outputs), $inputs);

    $sys_headers  = ['vector', 'tuple', 'array'];
    $user_headers = [];
    $lib_headers  = [];
    $libraries    = [];
    $properties   = ['list'];
    $extra        = [];
    $result_type  = ['multi']
?>

class <?=$className?>;

class <?=$className?> {
 public:
<?  if ($useArray) { ?>
  static const constexpr size_t kLength = <?=$numInputs?>;

  using InnerType = <?=$innerType?>;

  using ValueType = std::array<InnerType, kLength>;
<?  } else { ?>
  using ValueType = std::tuple<<?=typed($inputs)?>>;
<?  } ?>

  using Vector = std::vector<ValueType>;

 private:
  // The container that gathers the input.
  Vector items;

  // The tuple to be filled with the current item.
  ValueType item;

  // Used for iterating over the container during GetNextResult.
  int return_counter;

 public:
  <?=$className?>() {
    items.reserve(<?=$initSize?>);
  }

  void AddItem(<?=const_typed_ref_args($inputs)?>) {
<?  if ($useArray) { ?>
    items.push_back({<?=args($inputs)?>});
<?  } else { ?>
    items.push_back(std::make_tuple(<?=args($inputs)?>));
<?  } ?>
  }

  void AddState(<?=$className?>& other) {
    items.reserve(items.size() + other.items.size());
    items.insert(items.end(), other.items.begin(), other.items.end());
  }

  // The GLA uses a multi return type, meaning this is never implicitly called.
  void GetResult(<?=typed_ref_args($outputs)?>, int index = 0) const {
<?  foreach (array_keys($outputs) as $index => $name) { ?>
    <?=$name?> = std::get<<?=$index?>>(items[index]);
<?  } ?>
  }

  void Finalize() {
    return_counter = 0;
  }

  bool GetNextResult(<?=typed_ref_args($outputs)?>) {
    if (return_counter == items.size())
      return false;
    GetResult(<?=args($outputs)?>, return_counter);
    return_counter++;
    return true;
  }

  int GetCount() const {
    return items.size();
  }

  const Vector& GetList() const {
    return items;
  }
};

<?
    return [
        'kind'           => 'GLA',
        'name'           => $className,
        'system_headers' => $sys_headers,
        'user_headers'   => $user_headers,
        'lib_headers'    => $lib_headers,
        'libraries'      => $libraries,
        'properties'     => $properties,
        'extra'          => $extra,
        'iterable'       => false,
        'input'          => $inputs,
        'output'         => $outputs,
        'result_type'    => $result_type,
    ];
}
?>
