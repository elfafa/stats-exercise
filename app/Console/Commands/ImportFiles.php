<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File as FileSupport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use App\Answer;
use App\Area;
use App\Category;
use App\File;
use App\Percent;
use App\Question;
use App\Segment;

class ImportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import-files {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import files into database';

    private $answers;
    private $areas;
    private $categories;
    private $files;
    private $questions;
    private $segments;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // get list of already uploaded files
        $this->files = File::all()->keyBy('name');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = FileSupport::files(storage_path('import'));
        if ($files) {
            foreach ($files as $filepath)
            {
                $file = $this->createFile($filepath);
                if ($file) {
                    $this->loadAreas();
                    $this->loadCategories();
                    $this->loadQuestions();
                    $this->loadAnswers();
                    $this->loadSegments();
                    echo "Import of: ", $file->name, "\n";
                    Excel::load($filepath, function($reader) use ($file) {
                        echo " - preparation\n";
                        // we create areas
                        $this->createAreas($reader);
                        // we create categories
                        $this->createCategories($reader);
                        // we create questions
                        $this->createQuestions($reader);
                        // we create answers
                        $this->createAnswers($reader);
                        // we create segments
                        $this->createSegments($reader);
                        // we optimize memory
                        $this->areas      = null;
                        $this->categories = null;
                        $this->questions  = null;
                        // then: we create percents
                        echo " - treatment\n";
                        $bar = $this->output->createProgressBar(count($reader->get()));
                        $reader->each(function($row) use ($bar, $file) {
                            $this->createPercents($row, $file->id);
                            $bar->advance();
                        });
                        $bar->clear();
                        echo "  > ", count($reader->get()), " row(s) treated\n";
                    });
                    $this->moveFile($file);
                } else {
                    echo basename($filepath), " already treated\n";
                }
            }
        } else {
            echo "No file to import\n";
        }
    }

    private function createFile($filepath)
    {
        $filename = basename($filepath);
        if ($this->files && $this->files->has($filename)) {
            $file = $this->files->get($filename);
            if (File::STATUS_INPROGRESS === $file->status || $this->option('force')) {
                // delete previously entries for this file
                $file->percents()->delete();
                $file->updated_at = new \DateTime();
                $file->save();
            } else {
                // this file has already been treated
                return false;
            }
        } else {
            $file         = new File;
            $file->name   = $filename;
            $file->status = File::STATUS_INPROGRESS;
            $file->save();
        }

        return $file;
    }

    private function moveFile($file)
    {
        $file->status = File::STATUS_IMPORTED;
        $file->save();
        $storage = Storage::disk('import');
        $storage->move($file->name, 'done/'.$file->name);
    }

    private function loadAreas()
    {
        // get list of existing areas
        $this->areas = Area::get(['id', 'title'])->keyBy('title');
    }

    private function createAreas($reader)
    {
        $created = 0;
        for ($areaId = 1; $areaId < 3; $areaId++) {
            $areas = $reader->get(array('coverage_area_'.$areaId));
            if (! $areas->first()->isEmpty()) {
                foreach ($areas->unique('coverage_area_'.$areaId) as $columns) {
                    $property = 'coverage_area_'.$areaId;
                    if ($columns->$property && ! $this->areas->has($columns->$property)) {
                        $area = new Area;
                        $area->title = $columns->$property;
                        $area->save();
                        $this->loadAreas();
                        $created++;
                    }
                }
            } else {
                break;
            }
        }
        echo "  > ", $created, " area(s) created\n";
    }

    private function loadCategories()
    {
        // get list of existing categories
        $this->categories = Category::get(['id', 'title'])->keyBy('title');
    }

    private function createCategories($reader)
    {
        $created = 0;
        for ($categoryId = 1; $categoryId < 3; $categoryId++) {
            $categories = $reader->get(array('browse_category_'.$categoryId));
            if (! $categories->first()->isEmpty()) {
                foreach ($categories->unique('browse_category_'.$categoryId) as $columns) {
                    $property = 'browse_category_'.$categoryId;
                    if ($columns->$property && ! $this->categories->has($columns->$property)) {
                        $category = new Category;
                        $category->title = $columns->$property;
                        $category->save();
                        $this->loadCategories();
                        $created++;
                    }
                }
            } else {
                break;
            }
        }
        echo "  > ", $created, " category(ies) created\n";
    }

    private function loadQuestions()
    {
        // get list of existing questions
        $this->questions = Question::get(['id', 'code'])->keyBy('code');
    }

    private function createQuestions($reader)
    {
        $created   = 0;
        $questions = $reader->get(array('question_id', 'question'));
        foreach ($questions->unique('question_id') as $columns) {
            if ($columns->question_id && ! $this->questions->has($columns->question_id)) {
                $question = new Question;
                $question->title = $columns->question;
                $question->code = $columns->question_id;
                $question->save();
                $this->loadQuestions();
                $created++;
            }
        }
        echo "  > ", $created, " question(s) created\n";
    }

    private function loadAnswers()
    {
        // get list of existing answers
        $this->answers = Answer::get(['id', 'question_id', 'title'])->keyBy('title');
    }

    private function createAnswers($reader)
    {
        $created = 0;
        $answers = $reader->get(array('question_id', 'question_answer', 'coverage_area_1', 'coverage_area_2', 'browse_category_1', 'browse_category_2'));
        foreach ($answers->unique('question_answer') as $columns) {
            if ($columns->question_answer && ! $this->answers->has($columns->question_answer)) {
                $answer = new Answer;
                $answer->question_id = $this->questions[$columns->question_id]->id;
                $answer->title       = $columns->question_answer;
                $answer->save();
                if ($columns->coverage_area_1) {
                    $answer->areas()->attach($this->areas->get($columns->coverage_area_1)->id);
                    if ($columns->coverage_area_2) {
                        $answer->areas()->attach($this->areas->get($columns->coverage_area_2)->id);
                    }
                }
                if ($columns->browse_category_1) {
                    $answer->categories()->attach($this->categories->get($columns->browse_category_1)->id);
                    if ($columns->browse_category_2) {
                        $answer->categories()->attach($this->categories->get($columns->browse_category_2)->id);
                    }
                }
                $answer->save();
                $this->loadAnswers();
                $created++;
            }
        }
        echo "  > ", $created, " answer(s) created\n";
    }

    private function loadSegments()
    {
        // get list of existing segments
        $this->segments = Segment::all()->keyBy('code');
        if (! $this->segments->has('total')) {
            $segment        = new Segment;
            $segment->type  = Segment::TYPE_TOTAL;
            $segment->code  = 'total';
            $segment->title = 'Total';
            $segment->save();
            $this->loadSegments();
        }
        foreach ([ 'male', 'female' ] as $gender) {
            if (! $this->segments->has('gender_'.$gender)) {
                $segment        = new Segment;
                $segment->type  = Segment::TYPE_GENDER;
                $segment->code  = 'gender_'.$gender;
                $segment->title = ucfirst($gender);
                $segment->save();
                $this->loadSegments();
            }
        }
    }

    private function createSegments($reader)
    {
        $created = 0;
        $columns = $reader->first()->keys();
        foreach ($columns as $column) {
            $segment = null;
            if (false !== strpos($column, '_segment_')) {
                if (! $this->segments->has($column)) {
                    $strings           = explode('_segment_', $column);
                    $segment           = new Segment;
                    $segment->type     = Segment::TYPE_SEGMENT;
                    $segment->sub_type = $strings[0];
                    $segment->title    = ucwords(str_replace("_", " ", $strings[1]));
                }
            } elseif (0 === strpos($column, 'age_')) {
                if (! $this->segments->has($column)) {
                    $string         = str_replace('age_', '', $column);
                    $segment        = new Segment;
                    $segment->type  = Segment::TYPE_AGE;
                    $segment->title = ucwords(str_replace("_", " ", $string));
                }
            }
            if ($segment) {
                $segment->code = $column;
                $segment->save();
                $this->loadSegments();
                $created++;
            }
        }
        echo "  > ", $created, " segment(s) created\n";
    }

    private function createPercents($row, $fileId)
    {
        foreach ($row as $key => $value) {
            if ($value && $this->segments->has($key)) {
                $percent             = new Percent;
                $percent->file_id    = $fileId;
                $percent->answer_id  = $this->answers->get($row->question_answer)->id;
                $percent->segment_id = $this->segments->get($key)->id;
                $percent->value      = $value;
                $percent->save();
            }
        }
    }
}
