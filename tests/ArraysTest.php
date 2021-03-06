<?php

declare(strict_types = 1);

use Atomastic\Arrays\Arrays;

test('test create() method', function() {
    $this->assertEquals(new Arrays(), Arrays::create());
});

test('test arrays() helper', function() {
    $this->assertEquals(Arrays::create(), arrays());
});

test('test all() method', function() {
    $this->assertEquals(['SG-1', 'SG-2'], Arrays::create(['SG-1', 'SG-2'])->all());
});

test('test set() method', function() {
    $this->assertEquals(['stars' => ['Jack', 'Daniel', 'Sam']],
                        Arrays::create([])->set('stars', ['Jack', 'Daniel', 'Sam'])->all());
});

test('test get() method', function() {
    $this->assertEquals(['Jack', 'Daniel', 'Sam'],
                        Arrays::create(['stars' => ['Jack', 'Daniel', 'Sam']])->get('stars'));
    $this->assertEquals(['Jack', 'Daniel', 'Sam'],
                        Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]])->get('film.stars'));
    $this->assertEquals(['test'],
                        Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]])->get('film.scores', ['test']));
});

test('test has() method', function() {
    $this->assertTrue(Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam'], 'score' => ['5', '4']]])->has(['film.stars', 'film.score']));
    $this->assertTrue(Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]])->has('film.stars'));
    $this->assertFalse(Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]])->has('film.scores'));
});

test('test delete() method', function() {
    $array = Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]]);
    $array->delete('film.stars');
    $this->assertFalse($array->has('film.stars'));

    $array = Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam'], 'score' => ['5', '4']]]);
    $array->delete('film.stars');
    $array->delete('film.score');
    $this->assertFalse($array->has(['film.stars', 'film.score']));
});

test('test dot() method', function() {
    $this->assertEquals([
                            'movies.the_thin_red_line.title' => 'The Thin Red Line',
                            'movies.the_thin_red_line.directed_by' => 'Terrence Malick',
                            'movies.the_thin_red_line.produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                            'movies.the_thin_red_line.decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                         ],
                    Arrays::create([
                                    'movies' => [
                                        'the_thin_red_line' => [
                                            'title' => 'The Thin Red Line',
                                            'directed_by' => 'Terrence Malick',
                                            'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                                            'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                                        ],
                                    ],
                                 ])->dot()->all());
});

test('test undot() method', function() {
    $this->assertEquals([
                            'movies' => [
                               'the_thin_red_line' => [
                                   'title' => 'The Thin Red Line',
                                   'directed_by' => 'Terrence Malick',
                                   'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                                   'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                               ],
                            ],
                        ],
                    Arrays::create([
                                        'movies.the_thin_red_line.title' => 'The Thin Red Line',
                                        'movies.the_thin_red_line.directed_by' => 'Terrence Malick',
                                        'movies.the_thin_red_line.produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                                        'movies.the_thin_red_line.decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                                    ])->undot()->all());
});


test('test flush() method', function() {
    $arrays = Arrays::create()->set('stars', ['Jack', 'Daniel', 'Sam']);
    $arrays->flush();
    $this->assertEquals([], $arrays->all());
});

test('test sort() method', function() {
    // Default
    $arrays_original = [0 => ['title' => 'Post 1'],
                        1 => ['title' => 'Post 2']];

    $arrays_result = Arrays::create([1 => ['title' => 'Post 2'],
                                     0 => ['title' => 'Post 1']])->sortAssoc('title')->all();

    $array_equal = function($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

    // SORT ASC
    $arrays_original = [0 => ['title' => 'Post 1'],
                        1 => ['title' => 'Post 2']];

    $arrays_result = Arrays::create([1 => ['title' => 'Post 2'],
                                     0 => ['title' => 'Post 1']])->sortAssoc('title', 'ASC')->all();

    $array_equal = function($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

    // SORT DESC
    $arrays_original = [1 => ['title' => 'Post 2'],
                        0 => ['title' => 'Post 1']];

    $arrays_result = Arrays::create([1 => ['title' => 'Post 2'],
                                     0 => ['title' => 'Post 1']])->sortAssoc('title', 'DESC')->all();

    $array_equal = function($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

});

test('test count() method', function() {
    $this->assertEquals(3, Arrays::create(['Jack', 'Daniel', 'Sam'])->count());
    $this->assertEquals(1, Arrays::create(['names' => ['Jack', 'Daniel', 'Sam']])->count());
    $this->assertEquals(2, Arrays::create(['names' => ['Jack', 'Daniel', 'Sam'], 'tags' => ['star', 'movie']])->count('tags'));
    $this->assertEquals(2, Arrays::create(['collection' => ['names' => ['Jack', 'Daniel', 'Sam'], 'tags' => ['star', 'movie']]])->count('collection.tags'));
});
