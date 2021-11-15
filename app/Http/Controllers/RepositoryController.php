<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepositoryController extends Controller
{
    /**
     * @return array<array>
     */
    public function search(SearchRequest $request)
    {
        // Mauvaise pratique, ne prend pas en compte le required demandé dans le premier test
        //        if (!is_string(\Request::get('q'))) throw new Exception("The query parameter 'q' must be a string");
        //        if (strlen(request('q')) > 256) throw new Exception("The query parameter 'q' cannot be longer than 256 chars.", 422);

        $github = $this->github($request->q); // Appel la fontion github qui retourne un tableau

        $gitlab = $this->gitlab($request->q); // Appel la fonction gitlab qui retourne un table

        // Regroupe les 2 tableaux reçus et les place dans l'ordre gitlab puis github comme dans le test.
        //        return array_merge($this->gitlab($request->get('q')), $this->github($request->get('q')));
        return array_merge($gitlab, $github);
    }

    public function github($q)
    {
        $httpClient = new Client();

        try {
            $resp = $httpClient->get('https://api.github.com/search/repositories?per_page=5&q=' . $q);
        } catch (GuzzleException $e) {
            // Maybe we should return empty array if server error ?
            throw new Exception("Unable to contact API server.");
        }

        $repositories = json_decode($resp->getBody()->getContents(), true)['items'];

        // Explication du refactoring, voir gitlab
        $return = [];
        if ($repositories != null) {
            foreach ($repositories as $r) {
                $return[] = [
                    'repository' => $r['name'],
                    'full_repository_name' => $r['full_name'],
                    'description' => $r['description'],
                    // Je verifie si owner.username existe, si oui alors l'afficher si non, afficher owner.login
                    'creator' => isset($r['owner']['username']) ? $r['owner']['username'] : $r['owner']['login'],
                ];
            }
        }

        return $return;
    }

    public function gitlab($q)
    {
        $httpClient = new Client();

        try {
            $resp = $httpClient->get('https://gitlab.com/api/v4/projects?order_by=id&sort=asc&per_page=5&search=' . $q);
        } catch (GuzzleException $e) {
            // Maybe we should return empty array if server error ?
            throw new Exception("Unable to contact API server.");
        }

        // Ce bout de code est propre a github. je supprime ['items'] pour gitlab
        //        $repositories = json_decode($resp->getBody()->getContents(), true)['items'];
        // Le bon bout de code pour gitlab et sans le tableau ['items'}
        $repositories = json_decode($resp->getBody()->getContents(), true);

        /*** Ce bout de code stop l'execution de quand la variable repository est vide ou null**/

        //        if ($repositories == null) { //si le tableau est vide ou null
        //            // Maybe we should return empty array if parsing error ?
        //            throw new Exception("Unable to parse JSON resp."); ERREUR 500 et fin fin du code
        //        }
        //
        //        $return = [];
        //        foreach ($repositories as $r) {
        //            $return[] = [
        //                'repository' => $r['name'],
        //                'full_repository_name' => $r['full_name'],
        //                'description' => $r['description'],
        //                // Je verifie si owner.username existe, si oui alors l'afficher si non, afficher owner.login
        //                'creator' => isset($r['owner']['username']) ? $r['owner']['username'] : $r['owner']['login'],
        //            ];
        //        }
        //
        //        return $return;

        /*** Le code d'en haut ne renvoi pas de tableau vide mais une erreur 500 qui échoué les tests
         car gitlab renvoi une erreur 500 quand on lance une recherche de 256 caractere de a ***/



        // Par convention il faut renvoyé un tableau plein si les données sont bonnes ou un tableau vide dans tous les autres cas pour passé les tests
        $return = [];
        if ($repositories != null) {
            foreach ($repositories as $r) {
                $return[] = [
                    'repository' => $r['name'],
                    'full_repository_name' => $r['path_with_namespace'],
                    'description' => $r['description'],
                    // Je n'éffectue pas de verification comme l'indique le README
                    'creator' => $r['namespace']['path'],
                ];
            }
        }

        return $return;
    }
}
