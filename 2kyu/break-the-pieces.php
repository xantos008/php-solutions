/**
* Сама задача схожа с задачей из JS для определения островов в матрице.
* Для определения фигур можно представить основной поток диаграммы как матрицу
* Этот принцип я и буду использовать для решения задачи
* Если вы знаете способ изящнее, то, пожалуйста, не стесняйтесь и предлагайте
*/
class BreakPieces {
  private $matrix; // Создаем переменную для хранения матрицы
  private $shapeSizes; // Создаем переменную для хранения размеров фигуры

  public function process(string $shape): array { // shape уже предопределено задачей
    /**
     * Так как у нас у нас каждый новый элемент для массива начинается с новой строки
     * при помощи explode() указываем текстовый определитель переноса и получаем массив строк
     * а функция array_map() возвращает массив, содержащий результаты применения callback-функции str_split()
     * над полученным ранее массивом. По сути конструирую матрицу.
     */
    $this->matrix = array_map('str_split', explode("\n", $shape));
    $this->shapeSizes = []; // Задаем дефолтное значение
    $this->detectShapes();
    
    $shapes = [];
    foreach ($this->shapeSizes as $shapeId => $sizes) {
      $shapes[] = $this->buildShape($shapeId, $sizes);
    }

    return $shapes;
  }
  
  /**
   * Обнаруживает фигуры, помещая shapeID в каждую точку матрицы, принадлежащую фигуре
   */
  private function detectShapes(): void
  {
    $height = count($this->matrix); // Определяем высоту нашей матрицы, для дальнейшей "вырезки" фигуры
    $width = count($this->matrix[0]); // Определяем стартовую ширину фигуры

    // Вальяжная проходка по столбцам и строкам
    for ($y = 0; $y < $height; $y++) { // Столбцы
      for ($x = 0; $x < $width; $x++) { // Строки
        // Ругается компилятор, но можно и без этого условия
        if(isset($this->matrix[$y]) && isset($this->matrix[$y][$x])){
          // Находимся ли мы внутри фигуры
          if ($this->matrix[$y][$x] === ' ' && $this->isPointInsideShape($y, $x)) {
            $shapeId = count($this->shapeSizes);
            // Задаем координаты фигуры
            $this->shapeSizes[$shapeId] = [
              'maxX' => $x,
              'maxY' => $y,
              'minX' => $x,
              'minY' => $y,
            ];
            // Заполняем нашу красавицу
            $this->fillShape($y, $x, $shapeId);
          }
        }
      }
    }
  }
  
  /**
   * Заполните пустые точки(пробелы) внутри фигуры с тем же идентификатором shapeID
   */
  private function fillShape(int $y, int $x, int $shapeId): void
  {
    // Задали точку
    $this->matrix[$y][$x] = $shapeId;

    // Определяем движения, прям автострада :-)
    $this->shapeSizes[$shapeId]['maxY'] = max($this->shapeSizes[$shapeId]['maxY'], $y);
    $this->shapeSizes[$shapeId]['maxX'] = max($this->shapeSizes[$shapeId]['maxX'], $x);
    $this->shapeSizes[$shapeId]['minY'] = min($this->shapeSizes[$shapeId]['minY'], $y);
    $this->shapeSizes[$shapeId]['minX'] = min($this->shapeSizes[$shapeId]['minX'], $x);
    
    $directions = [
      [0, 1], // Спрва
      [1, 0], // Снизу
      [0, -1], // Слева
      [-1, 0], // Сверху
    ];
    // Вот и покрестились :-), да - да, ничего святого :-)

    // Поехали (с) Гагарин
    foreach ($directions as list($addY, $addX)) {
      $yy = $y + $addY;
      $xx = $x + $addX;
      if ($this->validPosition($yy, $xx) && $this->matrix[$yy][$xx] === ' ') {
        // Хейтеры скажут рекурсия :-)
        $this->fillShape($yy, $xx, $shapeId);
      }
    }
  }
  
  /**
   * Проверочка, находится ли точка внутри фигуры
   * По функциям все пояснения ниже, но вы это и так знали
   */
  private function isPointInsideShape(int $y, int $x): bool
  {
    return $this->hasWallOnTop($y, $x)
      && $this->hasWallOnBottom($y, $x)
      && $this->hasWallOnLeft($y, $x)
      && $this->hasWallOnRight($y, $x);
  }
  
  /**
   * Проверяем, есть ли стена на вершине точки
   */
  private function hasWallOnTop(int $y, int $x): bool
  {
    for ($yy = $y - 1; $yy >= 0; $yy--) {
      if ($this->isWall($this->matrix[$yy][$x])) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Проверяем, есть ли стена снизу точки
   */
  private function hasWallOnBottom(int $y, int $x): bool
  {
    for ($yy = $y + 1; $yy < count($this->matrix); $yy++) {
      if ($this->isWall($this->matrix[$yy][$x])) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Проверяем, есть ли стена слева от точки
   */
  private function hasWallOnLeft(int $y, int $x): bool
  {
    for ($xx = $x - 1; $xx >= 0; $xx--) {
      if ($this->isWall($this->matrix[$y][$xx])) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Проверяем, есть ли стена справа от точки
   */
  private function hasWallOnRight(int $y, int $x): bool
  {
    for ($xx = $x + 1; $xx < count($this->matrix[$y]); $xx++) {
      if ($this->isWall($this->matrix[$y][$xx])) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Це шо стена??
   * Проверяем, является ли значение стеной
   */
  private function isWall(string $val): bool
  {
    return strpos('|-+', $val) !== false;
  }
  
  /**
   * Хдэ я?
   * Проверяем, является ли точка действительной позицией в матрице
   * А то мы ребята без тормозов, можем и улететь, так что держимся в узде
   */
  private function validPosition(int $y, int $x): bool
  {
    return $x >= 0
      && $y >= 0
      && $x < count($this->matrix[0])
      && $y < count($this->matrix);
  }
  
  /**
   * Ну и, конечно же, строим нашу фигуру
   */
  private function buildShape(int $shapeId, array $sizes): string
  {
    // Здесь +3 богом дано нашими предками принтерович и вардампович
    $shapeWidth  = $sizes['maxX'] - $sizes['minX'] + 3;
    $shapeHeight = $sizes['maxY'] - $sizes['minY'] + 3;
        
    // Опустошили форму
    // Ибо нельзя наполнить наполненный сосуд (с) Джейсон Стетхэм
    $shape = [];
    for ($y = 0; $y < $shapeHeight; $y++) {
      $shape[$y] = str_repeat(' ', $shapeWidth);
    }

    // Далее пригодились годы дизайнерских решений из The Sims
    // Заполняем
    $height = count($this->matrix); // По традиции схватываем высоту
    $width = count($this->matrix[0]); // Определяем стартовый размер
    for ($y = 0; $y < $height; $y++) { // Столбцы
      for ($x = 0; $x < $width; $x++) { // Строки
        // Ну тут снова компилятор ругался за превышение, пришлось умаслить, не гаишнки, по этому ресурсы по минимуму хавает
        if(isset($this->matrix[$y]) && isset($this->matrix[$y][$x])){
          if ($this->matrix[$y][$x] === $shapeId) {
            $shape[$y - $sizes['minY'] + 1][$x - $sizes['minX'] + 1] = '.';
          }
        }
      }
    }
    
    // Строим стены
    for ($y = 0; $y < $shapeHeight; $y++) {
      for ($x = 0; $x < $shapeWidth; $x++) {
        if ($shape[$y][$x] !== ' ') {
          continue;
        }

        $hasPointLeftOrRight = ($shape[$y][$x + 1] ?? null) === '.'
          || ($shape[$y][$x - 1] ?? null) === '.';
        $hasPointTopOrBottom = ($shape[$y + 1][$x] ?? null) === '.'
          || ($shape[$y - 1][$x] ?? null) === '.';
        $hasPointDiagonal = ($shape[$y + 1][$x + 1] ?? null) === '.'
          || ($shape[$y + 1][$x - 1] ?? null) === '.'
          || ($shape[$y - 1][$x + 1] ?? null) === '.'
          || ($shape[$y - 1][$x - 1] ?? null) === '.';
        
        if (
          ($hasPointLeftOrRight && $hasPointTopOrBottom)
          || ($hasPointDiagonal && !$hasPointLeftOrRight && !$hasPointTopOrBottom)
        ) {
          $shape[$y][$x] = '+';
        } elseif ($hasPointLeftOrRight) {
          $shape[$y][$x] = '|';
        } elseif ($hasPointTopOrBottom) {
          $shape[$y][$x] = '-';
        }
      }
      $shape[$y] = rtrim($shape[$y]);
    }
    
    $strShape = implode("\n", $shape);
    
    // Надо вернуть все же в первозданном так что заменяем точки пробелами, таких у меня много :-)
    $strShape = strtr($strShape, '.', ' ');
    
    return $strShape;
  }
}