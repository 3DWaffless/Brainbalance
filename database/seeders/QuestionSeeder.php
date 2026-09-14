<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $mathTopic = Topic::firstOrCreate(
            ['subject' => 'Math', 'name' => 'Math Adaptive Quiz'],
            ['difficulty_level' => 'mixed', 'order_index' => 0]
        );

        $englishTopic = Topic::firstOrCreate(
            ['subject' => 'English', 'name' => 'English Adaptive Quiz'],
            ['difficulty_level' => 'mixed', 'order_index' => 0]
        );

        foreach ($this->mathQuestions() as $q) {
            $this->makeQuestion($mathTopic->id, $q);
        }

        foreach ($this->englishQuestions() as $q) {
            $this->makeQuestion($englishTopic->id, $q);
        }
    }

    protected function makeQuestion(int $topicId, array $q): void
    {
        Question::firstOrCreate(
            [
                'topic_id'      => $topicId,
                'question_text' => $q['text'],
            ],
            [
                'question_type'    => 'multiple_choice',
                'difficulty_level' => $q['level'],
                'correct_answer'   => $q['options'][$q['correct_index']],
                'options'          => $q['options'],
            ]
        );
    }

    protected function mathQuestions(): array
    {
        return [
            // Easy — Grades 1-2
            ['level' => 'easy', 'text' => 'What is 1 + 1?', 'options' => ['1', '2', '3', '4'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'What is 2 + 3?', 'options' => ['4', '5', '6', '7'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'What is 5 - 2?', 'options' => ['2', '3', '4', '5'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'How many sides does a triangle have?', 'options' => ['2', '3', '4', '5'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'Which number comes after 7?', 'options' => ['6', '7', '8', '9'], 'correct_index' => 2],
            ['level' => 'easy', 'text' => 'Which number is bigger, 8 or 5?', 'options' => ['5', '8', 'They are equal', 'Neither'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'How many fingers are on one hand?', 'options' => ['4', '5', '6', '10'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => 'What is 10 - 4?', 'options' => ['5', '6', '7', '8'], 'correct_index' => 1],

            // Medium — Grades 3-4
            ['level' => 'medium', 'text' => 'What is 6 x 3?', 'options' => ['16', '18', '20', '24'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => 'What is 24 ÷ 6?', 'options' => ['3', '4', '5', '6'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => 'What is 15 + 27?', 'options' => ['32', '38', '42', '45'], 'correct_index' => 2],
            ['level' => 'medium', 'text' => 'What is the perimeter of a square with sides of 4 cm?', 'options' => ['8 cm', '12 cm', '16 cm', '20 cm'], 'correct_index' => 2],
            ['level' => 'medium', 'text' => 'What is 100 - 37?', 'options' => ['53', '63', '67', '73'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => 'If a dozen has 12 items, how many are in 2 dozen?', 'options' => ['20', '22', '24', '26'], 'correct_index' => 2],
            ['level' => 'medium', 'text' => 'Which fraction is the same as one half?', 'options' => ['1/3', '2/4', '3/5', '1/5'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => 'What is 9 x 5?', 'options' => ['40', '45', '50', '54'], 'correct_index' => 1],

            // Hard — Grades 5-6
            ['level' => 'hard', 'text' => 'What is 3/4 written as a decimal?', 'options' => ['0.25', '0.34', '0.75', '0.43'], 'correct_index' => 2],
            ['level' => 'hard', 'text' => 'What is 10% of 200?', 'options' => ['10', '15', '20', '25'], 'correct_index' => 2],
            ['level' => 'hard', 'text' => 'What is the area of a rectangle 5 cm by 6 cm?', 'options' => ['11 cm²', '22 cm²', '30 cm²', '36 cm²'], 'correct_index' => 2],
            ['level' => 'hard', 'text' => 'What is 8 x 7?', 'options' => ['48', '54', '56', '64'], 'correct_index' => 2],
            ['level' => 'hard', 'text' => 'Simplify the fraction 4/8.', 'options' => ['1/2', '1/4', '2/3', '3/4'], 'correct_index' => 0],
            ['level' => 'hard', 'text' => 'What is the value of x in x + 8 = 15?', 'options' => ['5', '6', '7', '8'], 'correct_index' => 2],
        ];
    }

    protected function englishQuestions(): array
    {
        return [
            // Easy — Grades 1-2
            ['level' => 'easy', 'text' => "Which word is a noun: 'run', 'dog', 'fast', 'blue'?", 'options' => ['run', 'dog', 'fast', 'blue'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "Choose the correct spelling.", 'options' => ['freind', 'friend', 'frend', 'friand'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "What is the opposite of 'hot'?", 'options' => ['warm', 'cold', 'cool', 'wet'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "Which word means the same as 'happy'?", 'options' => ['sad', 'joyful', 'angry', 'tired'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "Which word rhymes with 'cat'?", 'options' => ['dog', 'hat', 'sun', 'pig'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "How many letters are in the word 'sun'?", 'options' => ['2', '3', '4', '5'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "Which one is a color?", 'options' => ['jump', 'red', 'table', 'happy'], 'correct_index' => 1],
            ['level' => 'easy', 'text' => "Which word is a plural (means more than one)?", 'options' => ['cat', 'cats', 'catty', "cat's"], 'correct_index' => 1],

            // Medium — Grades 3-4
            ['level' => 'medium', 'text' => "Identify the verb: 'The children played in the park.'", 'options' => ['children', 'played', 'park', 'the'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => "Which sentence is written correctly?", 'options' => ["he like apples", "He likes apples.", "he likes apples", "He like Apples."], 'correct_index' => 1],
            ['level' => 'medium', 'text' => "What is a synonym for 'quick'?", 'options' => ['slow', 'fast', 'lazy', 'tired'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => "Which punctuation mark ends a question?", 'options' => ['.', '!', '?', ','], 'correct_index' => 2],
            ['level' => 'medium', 'text' => "What type of word is 'quickly' in 'She ran quickly'?", 'options' => ['noun', 'verb', 'adjective', 'adverb'], 'correct_index' => 3],
            ['level' => 'medium', 'text' => "Which word is an antonym (opposite) of 'difficult'?", 'options' => ['hard', 'easy', 'tricky', 'complex'], 'correct_index' => 1],
            ['level' => 'medium', 'text' => "Which word describes a noun in 'the big red ball'?", 'options' => ['big', 'ball', 'the', 'a'], 'correct_index' => 0],

            // Hard — Grades 5-6
            ['level' => 'hard', 'text' => "Identify the figure of speech: 'The wind whispered through the trees.'", 'options' => ['Simile', 'Metaphor', 'Personification', 'Rhyme'], 'correct_index' => 2],
            ['level' => 'hard', 'text' => "Which sentence uses the past tense correctly?", 'options' => ['She finish her homework yesterday.', 'She finished her homework yesterday.', 'She finishing her homework yesterday.', 'She finishes her homework yesterday.'], 'correct_index' => 1],
            ['level' => 'hard', 'text' => "What is the main idea of a paragraph usually called?", 'options' => ['Supporting detail', 'Topic sentence', 'Conclusion', 'Conjunction'], 'correct_index' => 1],
            ['level' => 'hard', 'text' => "Choose the correctly written sentence.", 'options' => ['I like pizza, I like pasta.', 'I like pizza and I like pasta.', 'I like pizza but also pasta liking.', 'I like pizza, but I like pasta more!!'], 'correct_index' => 1],
            ['level' => 'hard', 'text' => "Which word is spelled correctly?", 'options' => ['necessary', 'neccessary', 'necesary', 'neccesary'], 'correct_index' => 0],
            ['level' => 'hard', 'text' => "What does the idiom 'break the ice' mean?", 'options' => ['To literally break ice', 'To start a conversation', 'To end a friendship', 'To be very cold'], 'correct_index' => 1],
        ];
    }
}
