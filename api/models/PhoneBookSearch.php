<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * PhoneBookSearch represents the model behind the search form of `app\models\PhoneBook`.
 */
class PhoneBookSearch extends PhoneBook
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(User $user, $params)
    {
        $lat = Arr::get($params, 'latitude');
        $lon = Arr::get($params, 'longitude');
        if ($lat && $lon) {
            return $this->searchNearby($user, $params);
        }

        $query = PhoneBook::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['<>', 'status', PhoneBook::STATUS_DELETED]);

        $query->andFilterWhere(['like', 'name', $params['search'] ?? null]);

        if (Arr::has($params, 'name')) {
            $query->andWhere(['like', 'name', Arr::get($params, 'name')]);
        }

        if (Arr::has($params, 'phone')) {
            $query->andWhere(['like', 'CAST(phone_numbers as CHAR)', Arr::get($params, 'phone')]);
        }

        if (Arr::has($params, 'status')) {
            $query->andWhere(['status' => Arr::get($params, 'status')]);
        }

        // Jika User
        if ($user->role <= User::ROLE_STAFF_RW) {
            return $this->getQueryRoleUser($user, $query, $params);
        }

        // For user kabkota, kec, kel
        if ($user->role >= User::ROLE_STAFF_KEL && $user->role <= User::ROLE_STAFF_KABKOTA) {
            return $this->getQueryRoleKabKotaKecKel($user, $query, $params);
        }

        // Else Has Admin Role, tampilkan semua
        return $this->getQueryAll($query, $params);
    }

    /**
     * Search using SqlDataProvider
     *
     * @param array $params
     *
     * @return SqlDataProvider
     */
    public function searchNearby(User $user, $params)
    {
        // Radius in kilometers
        $radius = 3.0;

        // Number of displayed results
        $limit = 20;

        $sql = file_get_contents(__DIR__ . '/scripts/getNearestByRadius.sql');
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':latitude' => Arr::get($params, 'latitude'),
                ':longitude' => Arr::get($params, 'longitude'),
                ':radius' => $radius,
                ':result_limit' => $limit,
            ],
        ]);
        $provider->setPagination(false);

        // Convert JSON string of phone_numbers
        $phonebooks = $provider->getModels();
        foreach ($phonebooks as &$phonebook) {
            $phonebook['phone_numbers'] = json_decode($phonebook['phone_numbers']);
        }
        $provider->setModels($phonebooks);

        return $provider;
    }

    protected function getQueryRoleUser($user, $query, $params)
    {
        $query->andFilterWhere(['<>', 'status', PhoneBook::STATUS_DISABLED]);

        // Jika memilih custom filter, akan override semua parameter default
        if ($this->isCustomFilter($params) === true) {
            ModelHelper::filterByArea($query, $params);
        } else {
            // Jika tidak memilih custom filter,
            // by default tampilkan daftar instansi di Kab/Kota dimana user tersebut tinggal
            $params['kabkota_id'] = Arr::get($user, 'kabkota_id');

            ModelHelper::filterByArea($query, $params);
        }

        if (Arr::has($params, ['search']) === false) {
            $query->orWhere([
                'and',
                ['kabkota_id' => null],
                ['kec_id' => null],
                ['kel_id' => null],
            ]);
        }

        return $this->getActiveDataProvider($query, $params);
    }

    protected function getQueryRoleKabKotaKecKel($user, $query, $params)
    {
        //Override kabkota_id must from their own city
        $params['kabkota_id'] = Arr::get($user, 'kabkota_id');

        ModelHelper::filterByArea($query, $params);

        return $this->getActiveDataProvider($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        ModelHelper::filterByArea($query, $params);

        return $this->getActiveDataProvider($query, $params);
    }

    protected function getActiveDataProvider($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'seq');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = $this->getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    protected function isCustomFilter($params)
    {
        return Arr::has($params, 'kabkota_id') || Arr::has($params, 'kec_id') || Arr::has($params, 'kel_id');
    }

    protected function getSortOrder($sortOrder)
    {
        switch ($sortOrder) {
            case 'descending':
                return SORT_DESC;
                break;
            case 'ascending':
            default:
                return SORT_ASC;
                break;
        }
    }
}
