<?php

namespace App\Http\Controllers\Quizzes;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quizzes\AttatchQuizBatchRequest;
use App\Http\Requests\Quizzes\AttatchQuizRequest;
use App\Http\Requests\Quizzes\SearchByCustomerQuizRequest;
use App\Http\Requests\Quizzes\SearchQuizRequest;
use App\Http\Requests\Quizzes\StoreQuizRequest;
use App\Jobs\UploadQuizImage;
use App\Models\Customer;
use App\Models\CustomerQuiz;
use App\Models\Quiz;

class QuizController extends Controller
{
    /**
     * Obtener una lista de todas las preguntas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchQuizRequest $request)
    {
        try {

            $data = $request->validated();

            $quizzes = Quiz::where('group_name', $data['group_name'])->get();

            return ApiResponseHelper::apiSuccess(200, 'Preguntas obtenidas exitosamente', ['quizzes' => $quizzes]);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de preguntas', $e->getMessage(), 500, 'GET_ACCESORIES_ERROR');
        }
    }

    /**
     * Obtener una lista de todas las preguntas a un customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchByCustomer(SearchByCustomerQuizRequest $request) //Modificar para obtener los valores de las respuestas
    {
        try {

            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            if (!$customer) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid para customer: ' . $data['customer_uuid'] ,404, 'GET_CUSTOMER_ERROR');
            }

            $quiz_ids = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48];

            $existing_quizzes = $customer->quizzes()->pluck('quiz_id')->toArray();

            

            $new_quiz_ids = array_diff($quiz_ids, $existing_quizzes);

            

            if (!empty($new_quiz_ids)) {

                $new_quizzes = Quiz::whereIn('id', $new_quiz_ids)
                                  ->orderBy('sort_id', 'asc')
                                  ->get();
        
                
                $customer->quizzes()->attach(
                    $new_quizzes->pluck('id')->toArray(),
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            $quizzes = $customer->quizzes()->orderBy('sort_id', 'asc')->get();

            $response = $quizzes->map(function($quiz) {
                return [
                    'uuid' => $quiz->uuid,
                    'name' => $quiz->name,
                    'description' => $quiz->description,
                    'values' => $quiz->values,
                    'status' => $quiz->status,
                    'question_type' => $quiz->question_type,
                    'element_type' => $quiz->element_type,
                    'group_name' => $quiz->group_name,
                    'image_path' => $quiz->image_path,
                    'selected_value' => $quiz->selected_value,
                    'created_at' => $quiz->created_at,
                ];
            });

            return ApiResponseHelper::apiSuccess(200, 'Preguntas obtenidas exitosamente', $response);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de preguntas', $e->getMessage(), 500, 'GET_ACCESORIES_ERROR');
        }
    }


    /**
     * Obtener una lista de todas las preguntas a un customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProfile()
    {
        try {

            $quiz_ids = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48];
            
            $quizzes = Quiz::whereIn('id', $quiz_ids)
                            ->orderBy('sort_id', 'asc')
                            ->get();

            $response = $quizzes->map(function($quiz) {
                return [
                    'uuid' => $quiz->uuid,
                    'name' => $quiz->name,
                    'description' => $quiz->description,
                    'values' => $quiz->values,
                    'status' => $quiz->status,
                    'question_type' => $quiz->question_type,
                    'element_type' => $quiz->element_type,
                    'group_name' => $quiz->group_name,
                    'image_path' => $quiz->image_path,
                    'selected_value' => $quiz->selected_value,
                    'created_at' => $quiz->created_at,
                ];
            });

            return ApiResponseHelper::apiSuccess(200, 'Preguntas obtenidas exitosamente', $response);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de preguntas', $e->getMessage(), 500, 'GET_ACCESORIES_ERROR');
        }
    }


    /**
     * Actualizar pregunta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            
            $data = $request->validated();

            $quiz = Quiz::firstOrCreate(['name' => $data['name'], 'question_type' => $data['question_type'],'group_name' => $data['group_name']]);

            $quiz->update([
                'description' => $data['description'],
                'values' => $data['values'],
                'status' => $data['status'],
                'element_type' => $data['element_type'],
            ]);

            if(isset($data['image'])) {

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadQuizImage::dispatchSync($path, $quiz, $image->getClientOriginalName());
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Pregunta de cuestionario creada correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la pregunta del cuestionario', $e->getMessage(), 500, 'QUIZ_CREATE_ERROR');
        }
    }

    /**
     * Actualizar pregunta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatch(AttatchQuizRequest $request)
    {
        try {
            
            $data = $request->validated();

            $quiz = Quiz::findByUuid($data['quiz_uuid']);

            $customer = Customer::findByUuid($data['customer_uuid']);

            if (!$quiz || !$customer) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['quiz_uuid'] .' '.$data['customer_uuid'] ,404, 'GET_QUIZ_CUSTOMER_ERROR');
            }

            $customer_quiz = CustomerQuiz::firstOrCreate(['quiz_id' => $quiz->id, 'customer_id' => $customer->id]);

            $customer_quiz->update(['selected_value' => $data['selected_value']]);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Cuestionario asociado correctamente al cliente:');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular cuestionario con cliente', $e->getMessage(), 500, 'QUIZ_CUSTOMER_ATTATCH_ERROR');
        }
    }


    /**
     * Actualizar pregunta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatchBatch(AttatchQuizBatchRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            if (!$customer) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['quiz_uuid'] .' '.$data['customer_uuid'] ,404, 'GET_QUIZ_CUSTOMER_ERROR');
            }

            foreach ($data['quiz_uuids'] as $index => $quiz_uuid) {

                $quiz = Quiz::findByUuid($quiz_uuid);

                $customer_quiz = CustomerQuiz::firstOrCreate(['quiz_id' => $quiz->id, 'customer_id' => $customer->id]);

                $customer_quiz->update(['selected_value' => $data['selected_values'][$index]]);
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Cuestionario asociado correctamente al cliente:');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular cuestionario con cliente', $e->getMessage(), 500, 'QUIZ_CUSTOMER_ATTATCH_ERROR');
        }
    }

}